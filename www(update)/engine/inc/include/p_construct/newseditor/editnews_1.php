<?php

include_once (ENGINE_DIR . '/inc/include/p_construct/config.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/constants.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/vkvideolib.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VideoConstructor.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VideoTubes.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VcExtensionConfig.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VcExtension.php');
VcExtension::getInstance()->init();

$data = VideoConstructor::getInstance()->getDataForEditor(intval($_GET['id']));
VideoConstructor::getInstance()->runEditor($data);
unset($data);

