<?php

namespace Argus;

use Symfony\Component\Process\Process;
use RuntimeException;

class ImageComparer
{
    protected $process;
    protected $command = 'compare';

    public function __construct()
    {
        $this->process = new Process($this->command);
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
        $deleteSource = false;
        $size1 = getimagesize($image1);
        $size2 = getimagesize($image2);
        if ($size1[0] !== $size2[0] || $size1[1] !== $size2[1]) {
            // If images isn't the same height, scale to a common size as
            // the compare command requires equal sized images.
            $width = max($size1[0], $size2[0]);
            $height = max($size1[1], $size2[1]);
            $deleteSource = true;
            $image1 = $this->makeTmpImage($image1, $width, $height);
            $image2 = $this->makeTmpImage($image2, $width, $height);
        }

        $this->process->setCommandLine(implode(' ', [
            $this->command,
            '-dissimilarity-threshold 1',
            '-fuzz 1',
            '-metric AE',
            '-highlight-color blue',
            escapeshellarg($image1),
            escapeshellarg($image2),
            escapeshellarg($diff),
        ]));

        $this->process->mustRun();

        if ($deleteSource) {
            unlink($image1);
            unlink($image2);
        }
        $pixelCount = trim($this->process->getErrorOutput());
        if (!preg_match('/\d+/', $pixelCount)) {
            throw new RuntimeException("Unexpected output from compare: " . $pixelCount);
        }

        $pixelCount = (int) $pixelCount;
        $size = getimagesize($diff);
        $percentage = ($pixelCount / ($size[0] * $size[1])) * 100;
        return $percentage;
    }

    /**
     * Create temporary image at specific size.
     */
    protected function makeTmpImage($image, $width, $height) {
        $geometry = $width . 'x' . $height;
        $tmp = sys_get_temp_dir() . '/argus_' . getmypid() . '_' . basename($image);
        $this->process->setCommandLine(implode(' ', [
            'convert',
            '-composite',
            '-size ' . $geometry,
            '-gravity northwest',
            '-crop ' . $geometry . '+0+0',
            'canvas:none',
            escapeshellarg($image),
            escapeshellarg($tmp)
        ]));
        $this->process->mustRun();
        return $tmp;
    }

    /**
     * Set compare command to run.
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }
}
