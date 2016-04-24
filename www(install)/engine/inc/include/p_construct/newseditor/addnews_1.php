<?php

require_once (ENGINE_DIR . '/inc/include/p_construct/config.php');
require_once (ENGINE_DIR . '/inc/include/p_construct/constants.php');
require_once (ENGINE_DIR . '/inc/include/p_construct/classes/vkvideolib.php');
// Загрузка библиотек JSON для PHP<5.2.0
if (!is_callable("json_encode")) {
    include_once (ENGINE_DIR . '/inc/include/p_construct/classes/JSON.php');
}
require_once (ENGINE_DIR . '/inc/include/p_construct/classes/VideoConstructor.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VideoTubes.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VcExtensionConfig.php');
include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VcExtension.php');
VcExtension::getInstance()->init();

if ($vk_config['is_debug']==1) error_reporting (E_ALL);

VideoConstructor::getInstance()->runEditor(array());
unset($data);

