Mirror
======

Mirror a source directory to a destination directory updating only modified files.

    php bin/console site:mirror /path/to/source/dir /path/to/destination/dir
 
It compare the content of each file of the source dir with the files in the destination dir and update the 
destination if needed. At the end if remove all files in the destination dir that is not in the source dir.

Use
---------------

    php bin/console site:mirror /path/to/source/dir /path/to/destination/dir

This command just simulate the mirroring, if you want to actualy do it, add the `--go` option.

    php bin/console site:mirror --go /path/to/source/dir /path/to/destination/dir
