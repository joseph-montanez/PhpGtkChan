<?php 
class Gorilla3D_Filesystem
{
    /**
     * Safely build a file path thats cross platform
     *
     * @params mixed $path, [, $path]
     */
    public static function buildPath($path = null)
    {
        if(!is_array($path)) {
            $path = func_get_args();
        }
        return implode(DIRECTORY_SEPARATOR, $path);
    }
}
?>
