<?php
use \FunctionalTester;
use \Argus\ImageComparer;

class ImageComparerCest
{
    protected $workDir;

    public function _before(FunctionalTester $I)
    {
        $this->workDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'argus_test' . md5(microtime() * rand(0, 10000));
        if (is_dir($this->workDir)) {
            self::clearDirectory($this->workDir);
        }
        mkdir($this->workDir);
    }

    public function _after(FunctionalTester $I)
    {
        if (is_dir($this->workDir)) {
            self::clearDirectory($this->workDir);
        }
    }

    // tests
    public function tryToTest(FunctionalTester $I)
    {

        $I->amInPath($this->workDir);
        copy(codecept_data_dir() . 'image1.png', $this->workDir . '/image1.png');
        copy(codecept_data_dir() . 'image2.png', $this->workDir . '/image2.png');
        $comparer = new ImageComparer();

        // Check difference between the same image.
        $difference = $comparer->difference('image1.png', 'image1.png', 'image.diff.png');
        $I->assertTrue($difference === 0);
        $I->assertTrue(file_exists('image.diff.png'));
        unlink('image.diff.png');

        // Compare different images.
        $difference = $comparer->difference('image1.png', 'image2.png', 'image.diff.png');
        $I->assertTrue($difference > 0);
        codecept_debug($difference);
        $I->assertTrue(file_exists('image.diff.png'));

        // Check missing command.
        $comparer->setCommand('this_command_doesnt_exist');
        try {
            $difference = $comparer->difference('image1.png', 'image2.png', 'image.diff.png');
            $I->fail("Bad command didn't throw an Exception");
        } catch (Exception $e) {
            // All good.
        }
    }

    private static function clearDirectory($path)
    {
        $files = scandir($path);
        array_shift($files);
        array_shift($files);

        foreach ($files as $file) {
            $file = $path . DIRECTORY_SEPARATOR . $file;
            if (is_dir($file)) {
                self::clearDirectory($file);
            } else {
                unlink($file);
            }
        }

        rmdir($path);
    }

}
