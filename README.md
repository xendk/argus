
Argus Panoptes
==============

This is a visual testing module for Codeception.

Usage
-----

Install the module using composer and add Argus, together with
WebDriver, to the modules in your suite.yml file, and sprinkle
`$I->eyeball("filename/description")`  through your test.

Run your tests and they should fail due to new screen-shots. Look
through all the `*.result.png` files and make sure they look as they
should, and rename them to `*.original.png`.

Now Argus will compare the screen-shots of new test runs with the
originals and fail if detecting any differences. It will also leave the
new `*.result.png` file and a `*.diff.png` highlighting the
differences for inspection. Replace the `*.original.png` file with the
corresponding `*.result.png` to accept the changes, or fix the error.
