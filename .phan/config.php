<?php

/**
 * Phan static analysis configuration file.
 * @see https://github.com/phan/phan/blob/master/.phan/config.php
 */

return [
    "directory_list" => [
        "src"
    ],
    "exclude_file_regex" => "/Test\.php$/i",
    "exclude_analysis_directory_list" => [
        "vendor"
    ],
];
