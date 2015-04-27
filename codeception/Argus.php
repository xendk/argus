<?php

namespace Codeception\Module;

use Codeception\TestCase;
use Argus\ImageComparer;

class Argus extends \Codeception\Module
{
    const SUFFIX = '.original.png';
    const NEWSUFFIX = '.result.png';
    const DIFFSUFFIX = '.diff.png';

    protected $comparer = null;

    protected $changes = [];

    /**
     * Run visual inspection on browser.
     */
    public function eyeball($name)
    {
        $filebase = codecept_output_dir() . $name;
        $dirname = dirname($filebase);
        if (!file_exists($dirname)) {
            mkdir($dirname, 0777, true);
        }

        $oldFile = $filebase . self::SUFFIX;
        $newFile = $filebase . self::NEWSUFFIX;
        $diffFile = $filebase . self::DIFFSUFFIX;

        $this->getModule('WebDriver')->_saveScreenshot($newFile);
        if (file_exists($oldFile)) {
            if ($this->comparer->difference($oldFile, $newFile, $diffFile) > 0) {
                $this->changes[] = $filebase;
            } else {
                // Remove the new and diff when no changes detected.
                unlink($newFile);
                unlink($diffFile);
            };
        } else {
            // New file, add it to the list.
            $this->changes[] = $filebase;
        }
    }

    /**
     * Initialization.
     */
    public function _initialize()
    {
        $this->comparer = new ImageComparer();
    }

    /**
     * Called after suite.
     *
     * Fail the suite and output changes, if any differences was detected.
     */
    public function _afterSuite()
    {
        // Filter out files that's disappeared. This allows for processing
        // the files before we've finished the test run.
        $changes = array_filter($this->changes, function ($file) {
            return file_exists($file . self::NEWSUFFIX);
        });

        if (!empty($changes)) {
            $fileLines = array_map(function($file) {
                $short_name = preg_replace('/^' . preg_quote(codecept_root_dir(), '/') . '/', '', $file);
                if (file_exists($file . self::SUFFIX)) {
                    return "Changed: " . $short_name . self::NEWSUFFIX;
                }
                return "New: " . $short_name . self::NEWSUFFIX;
            }, $changes);
            $message = "Visual changes detected:\n";
            $message .= implode("\n", $fileLines);
            $this->fail($message);
        }
    }
}
