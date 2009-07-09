<?php
function unlinkRecursive($dir) {
	if(!$dh = @opendir($dir)){return;}
    while (false !== ($obj = readdir($dh))) {
		if($obj == '.' || $obj == '..') {continue;}
        if (!@unlink($dir . '/' . $obj)){
			unlinkRecursive($dir.'/'.$obj);
		}
    }
    closedir($dh);
    return;
} 

echo unlinkRecursive('cache');
echo $_REQUEST['count'].' files deleted. Cache cleared.';
?>