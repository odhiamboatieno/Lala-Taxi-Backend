<?php

// Read-only comparison of tracked application files, never uploads or storage.
$source = $argv[1] ?? '/src';
$target = $argv[2] ?? '/var/www/html';
$previous = $target.'/.src-files';
$next = $source.'/.src-files';
if (!is_file($next)) {
    fwrite(STDERR, "Missing release file manifest.\n");
    exit(1);
}
if (!is_file($previous)) {
    fwrite(STDOUT, "First manifest deployment: compare previous Git release separately; no files removed.\n");
    exit(0);
}
$old = file($previous, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$new = file($next, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$failed = false;
foreach (array_diff($old, $new) as $path) {
    if (!preg_match('~^(app|bootstrap|config|database|Modules|routes|resources|public)/[a-zA-Z0-9_./ -]+$~', $path)
        || str_contains($path, '..') || str_contains($path, '//')
        || preg_match('~^(bootstrap/cache|public/images|public/trip_file)/~', $path)) {
        fwrite(STDERR, "Unsafe prior manifest entry; release requires investigation.\n");
        $failed = true;
        continue;
    }
    if (is_file($target.'/'.$path) || is_link($target.'/'.$path)) {
        fwrite(STDERR, "Obsolete code requires backup and explicit audit: ".$path."\n");
        $failed = true;
    }
}
exit($failed ? 1 : 0);
