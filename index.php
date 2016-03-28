<!-- 
              ____________________________________________________                             
                ______             _    _   _____    _   _                                     
                  /      /         |   /    /    )   /  /| /                                   
              ---/------/__----__--|--/----/----/---/| /-|-----__-                                             
                /      /   ) /___) | /    /    /   / |/  |    (_ `             
              _/______/___/_(___ __|/____/____/___/__/___|___(__)_                             
 _________________________________________________________________________________
       ____                                __                                     
  /    /    )   /                          / |                /     ,           / 
 -----/____/---/__----__--_/_----__-------/__|---)__----__---/__-------------__---
     /        /   ) /   ) /    /   )     /   |  /   ) /   ' /   ) /   | /  /___)  
 ___/________/___/_(___/_(_ __(___/_____/____|_/_____(___ _/___/_/____|/__(___ ___
 
    TheVDM's Photo Archive 1.3 Copyright (c) J.Valentine http://thevdm.com
    http://thevdm.com/Photo_Archive.html
    This copyright section must not be removed.
 
-->
<?php if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], ‘gzip’)) ob_start(“ob_gzhandler”); else ob_start(); ?>
<?php
// set the maximum number of images per page
$maxImages = 50;
// Function to resize images to generate thumbnail
define('THUMBNAIL_IMAGE_MAX_WIDTH', 150);
define('THUMBNAIL_IMAGE_MAX_HEIGHT', 100);

function generate_image_thumbnail($source_image_path, $thumbnail_image_path)
{
    list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);
    switch ($source_image_type) {
        case IMAGETYPE_GIF:
            $source_gd_image = imagecreatefromgif($source_image_path);
            break;
        case IMAGETYPE_JPEG:
            $source_gd_image = imagecreatefromjpeg($source_image_path);
            break;
        case IMAGETYPE_PNG:
            $source_gd_image = imagecreatefrompng($source_image_path);
            break;
    }
    if ($source_gd_image === false) {
        return false;
    }
    $source_aspect_ratio = $source_image_width / $source_image_height;
    $thumbnail_aspect_ratio = THUMBNAIL_IMAGE_MAX_WIDTH / THUMBNAIL_IMAGE_MAX_HEIGHT;
    if ($source_image_width <= THUMBNAIL_IMAGE_MAX_WIDTH && $source_image_height <= THUMBNAIL_IMAGE_MAX_HEIGHT) {
        $thumbnail_image_width = $source_image_width;
        $thumbnail_image_height = $source_image_height;
    } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
        $thumbnail_image_width = (int) (THUMBNAIL_IMAGE_MAX_HEIGHT * $source_aspect_ratio);
        $thumbnail_image_height = THUMBNAIL_IMAGE_MAX_HEIGHT;
    } else {
        $thumbnail_image_width = THUMBNAIL_IMAGE_MAX_WIDTH;
        $thumbnail_image_height = (int) (THUMBNAIL_IMAGE_MAX_WIDTH / $source_aspect_ratio);
    }
    $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
    imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);
    imagejpeg($thumbnail_gd_image, $thumbnail_image_path, 90);
    imagedestroy($source_gd_image);
    imagedestroy($thumbnail_gd_image);
    return true;
}
// End of function
?>
<Doctype html!>
<html>
<head>
    <title>Photo Archive</title>
    <meta name="web_author" content="J.Valentine (www.thevdm.com)">
    <!-- The copyright section must not be changed -->
    <meta name="copyright" content="J.Valentine (www.thevdm.com)">  
    <link rel="stylesheet" href="inc/style.css">
    <!-- Load Colorbox -->
    <link rel="stylesheet" href="inc/colorbox.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="inc/jquery.colorbox.js"></script>
    <!-- Set Colorbox modes -->
    <script>
        $(document).ready(function(){
            //Examples of how to assign the Colorbox event to elements
            $(".photos").colorbox({rel:'photos',maxWidth:'90%',maxHeight:'90%'});
            $(".description").colorbox({iframe:true, width:"80%", height:"80%"});
        });
    </script>
</head>
<body>
<!-- Header -->
<div id="header">
    <a href='?'><span id="site_title">Photo Archive</span></a>
</div>
<!-- The container is set to static with auto Y scroll, the whole page never moves in the browser, just the content of this div -->
<div id="container">
    <?php
    // Check if a folder is set via the URL
    if(isset($_GET['folder']) && ($_GET['folder'] != '')){
        // Remove '../' from the strin to stop attempted hacks
        $folder = str_replace('../', '', $_GET['folder']);
        // Add the images folder to the beginning of the string
        $folder = 'images/' . $folder . '/';
    } else {
        // If no folder is set just use the images/ folder
        $folder = 'images/';
    }
    // Show a bread crumb in the header
    echo "<div id='breadcrumb'><span style='color:#fff'>Viewing: $folder</span></div>";
    // If your in a sub folder show an 'Up one level' button
    if($folder != 'images/') {
        // Remove the preceding / from the string (i.e. last character)
        $upOne = substr($folder, 0, -1);
        // Explode the string wherever a / appears (i.e. separate the folders)
        $folderExp = explode("/", $upOne);
        // Get the last section of the explode (i.e. the current folder name)
        $end = end($folderExp);
        // Remove the 'images/' section from the front of the string
        $upOne = str_replace('images/', '', $upOne);
        // Remove the last folder name
        $upOne = str_replace($end, '', $upOne);
        // Remove the preceding / from the string (i.e. the last character)
        $upOne = substr($upOne, 0, -1);
        // Show a link to go up one folder
        echo "<a href='?folder=$upOne' title='Up one level'>";
        echo "<div class='upone'><img src='inc/up_level.png'></div>";
        echo "</a>";
    }
    /* List the folders */
    foreach(glob("$folder*", GLOB_ONLYDIR) as $dir) {
        // Get the name of the folder by removing the current folder string
        $dirShort = str_replace($folder, '', $dir);
        // Remove 'images/' from the beginning of the string
        $dir = str_replace('images/', '', $dir);
        // Show a link for each folder
        echo "<a href='?folder=$dir' title='images/$dir/' class='folder'>";
        echo "<div>";
        echo "<span>$dirShort</span></div>";
        echo "<span>Preview of $dirShort:<br>";
        // Show the first 10 thumbnails from the album
        foreach(array_slice(glob("images/$dir/{*.jpg,*.gif,*.png,*.tif,*.jpeg}", GLOB_BRACE), 0, 9) as $entry) {
            $thumb = 'thumbs' . substr($entry, 6);
            if (file_exists($thumb)) {
                $entry = $thumb;
            }
            echo "<div style='background:url(\"inc/loading_preview.gif\"); background-repeat: no-repeat; background-size:contain;'>";
            echo "<div style='background:url(\"$entry\"); background-repeat: no-repeat; background-size:cover;'></div>";
            echo "</div>";
        }
        echo "</span>";
        echo "</a>";
    }
    /* Search for text files within the folder and generate an information link for each */
    foreach(glob("$folder*.txt", GLOB_BRACE) as $info) {
        // Explode the sting which contains the path to the image
        $fileName = explode("/", $info);
        // Get the last section of the sting (i.e. the image name)
        $fileName = end($fileName);
        echo "<a href='$info' class='description' title='$fileName'>";
        echo "<div><img src='inc/information.png'><br><span>$fileName</span></div>";
        echo "</a>";
    }
    // Count the images$folder
    $count = 0;
    foreach(glob("$folder{*.jpg,*.gif,*.png,*.tif,*.jpeg}", GLOB_BRACE) as $entry) {
        $count = $count + 1;
    }
    // if there are more than 50 images show the page buttons
    if($count > $maxImages) {
        echo "<div id='navinfo'>Use the arrow keys (or B/N) to navigate through the pages</div>";
        // Get the page no from the URL if its set, otherwise set it to 1
        if(isset($_GET['page']) && is_numeric($_GET['page'])) {
            $page = $_GET['page'];
        } else {
            $page = 1;
        }
        // work out the first image to show from the page number
        $start = ($page - 1);
        $start = ($start * $maxImages);
        // Work out the total number of pages
        $noPages = ceil($count / $maxImages);
        // Get the current folder
        $currFolder = str_replace('images/', '', $folder);
        // Remove the last character (the trailing /)
        $currFolder = substr($currFolder, 0, -1);
        // If the page isn't 1 show the previous page link
        if($page != 1) {
            $previous = ($page - 1);
            echo "<a href='?folder=$currFolder&page=$previous' title='Previous page'><div id='previous'></div></a>";
            echo "<script type='text/javascript'>
                $(document).keydown(function(e){
                    if ((e.keyCode == 37) || (e.keyCode == 66)) {
                        window.location.href = '?folder=$currFolder&page=$previous';
                        return false;
                    }
                });
            </script>";
        }
        // If the page isn't the last one show the next link
        if($page != $noPages) {
            $next = ($page + 1);
            echo "<a href='?folder=$currFolder&page=$next' title='Next page'><div id='next'></div></a>";
            echo "<script type='text/javascript'>
                $(document).keydown(function(e){
                    if ((e.keyCode == 39) || (e.keyCode == 78)) {
                        window.location.href = '?folder=$currFolder&page=$next';
                        return false;
                    }
                });
            </script>";
        }
        // Work out the last image for the to from header section
        $to = ($start + $maxImages);
        $from = ($start + 1);
        if ($to > $count) {
            // If to is greater than the number of images, show the number of images
            $to = $count;
        }
        $toFrom = "$from to $to of ";
    } else {
        $start = 0;
        // Create the to and from images for the header
        if($count != 0) {
            $toFrom = "1 to $count of ";
        } else {
            $toFrom = "";
        }
    }
    // Show number of images in folder within the header
    echo "<div id='image_count'>$toFrom$count images</div>";
    /* List the images in the directory */
    foreach(array_slice(glob("$folder{*.jpg,*.gif,*.png,*.tif,*.jpeg}", GLOB_BRACE), $start, $maxImages) as $entry) {
        $exif_data = exif_read_data ($entry);
        $exif = '';
        if (!empty($exif_data['DateTimeOriginal'])) {
            $exif = 'Date (' . $exif_data['DateTimeOriginal'] . ')';
        }
        if (!empty($exif_data['Make'])) {
            $exif = $exif . ' Equipment (' . $exif_data['Make'] . ' | ' . $exif_data['Model'] . ')';
        }
        if ($exif != '') {
            $exif = $exif . ' - ';
        }
        
        // Explode the sting which contains the path to the image
        $imgName = explode("/", $entry);
        // Get the last section of the sting (i.e. the image name)
        $imgName = end($imgName);
        // Set the thumbnail directory
        $thumbDir = 'thumbs' . str_replace($imgName, '', substr($entry, 6));
        if (!file_exists($thumbDir)) {
            // If the directory doesn't exist, create it
            mkdir($thumbDir, 0777, true);
        }
        // Set the name of the thumbnail
        $thumb = 'thumbs' . substr($entry, 6);
        if (!file_exists($thumb)) {
            // If the thumb doesn't exist create it by copying the original file and resizing it
            generate_image_thumbnail($entry, $thumb);
        }
        // Show a thumb nail and Lightbox link for each image
        echo "<a href='$entry' class='photos' title='$exif$imgName'>";
        echo "<div><img src='$thumb'><br><span>$imgName</span></div>";
        echo "</a>";
    }
    ?>
</div>
<!-- Footer -->
<div id="footer">
    <span>Copyright &copy; J.Valentine (www.thevdm.com) - Powered by: <a href='http://thevdm.com/Photo_Archive.html'>Photo Archive</a></span>
</div>
</body>
</html>