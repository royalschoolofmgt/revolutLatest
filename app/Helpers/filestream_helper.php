<?php

/**
 * This file is part of 247Commerce BigCommerce Revolut App.
 *
 * (c) 2021 247 Commerce Limited <info@247commerce.co.uk>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

/**
 * Class filestream_helper
 *
 * Represents a helper class to save or create folders and files in project directory
 */
class filestream_helper
{
    /**
     * saveFile - to save file in 247commerce path
     *
     * @param $filename
     * @param $filecontent
     * @param $folderPath
     *
     * @return status of save file in path
     */
    public static function saveFile($filename, $filecontent, $folderPath = '')
    {
        if (strlen($filename) > 0) {
            if (! file_exists($folderPath)) {
                $permit = 0777;
                mkdir($folderPath);
                chmod($folderPath, $permit);
            }
            $file = @fopen($folderPath . DIRECTORY_SEPARATOR . $filename, 'wb');
            if ($file !== false) {
                //file_put_contents($folderPath . DIRECTORY_SEPARATOR . $filename, "$filecontent");
                fwrite($file, $filecontent);
                fclose($file);

                return 1;
            }

            return -2;
        }

        return -1;
    }
}
