<?php

namespace Argus;

use Symfony\Component\Process\Process;
use RuntimeException;

class ImageComparer
{
    protected $imagemagick;
    protected $command = 'compare';

    public function __construct()
    {
        $this->imagemagick = new Process($this->command);
    }
    /**
     * Compare two images and return whether there's any differences.
     *
     * @param string $image1
     *   Path to first image.
     * @param string $image2
     *   Path to second image.
     * @param string $diff
     *   Path to output diff image.
     *
     * @return int
     *   Difference as a percentage.
     */
    public function difference($image1, $image2, $diff)
    {
        $options = '-dissimilarity-threshold 1 -fuzz 1 -metric AE -highlight-color blue';
        $this->imagemagick->setCommandLine(implode(' ', array(
            $this->command,
            $options,
            escapeshellarg($image1),
                escapeshellarg($image2),
            escapeshellarg($diff),
        )));

        $this->imagemagick->mustRun();
        $pixelCount = trim($this->imagemagick->getErrorOutput());
        if (!preg_match('/\d+/', $pixelCount)) {
            throw new RuntimeException("Unexpected output from compare: " . $pixelCount);
        }
        $pixelCount = (int) $pixelCount;
        $size = getimagesize($diff);
        $percentage = ($pixelCount / ($size[0] * $size[1])) * 100;
        return $percentage;
    }

    /**
     * Set compare command to run.
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }
}
