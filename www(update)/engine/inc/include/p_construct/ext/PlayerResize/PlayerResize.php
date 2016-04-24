<?php

global $vk_config, $CvStruct, $CvInfo;
if ($CvInfo["scount"] == 1) {
    if ($vk_config["extensions"]["PlayerResize"]["config"]["o_width"])
        $vk_config['player_width'] = $vk_config["extensions"]["PlayerResize"]["config"]["o_width"];
    if ($vk_config["extensions"]["PlayerResize"]["config"]["o_height"])
        $vk_config['player_height'] = $vk_config["extensions"]["PlayerResize"]["config"]["o_height"];
} else if ($CvInfo["zcount"] == 1) {
    if ($vk_config["extensions"]["PlayerResize"]["config"]["s_width"])
        $vk_config['player_width'] = $vk_config["extensions"]["PlayerResize"]["config"]["s_width"];
    if ($vk_config["extensions"]["PlayerResize"]["config"]["s_height"])
        $vk_config['player_height'] = $vk_config["extensions"]["PlayerResize"]["config"]["s_height"];
} else {
    if ($vk_config["extensions"]["PlayerResize"]["config"]["m_width"])
        $vk_config['player_width'] = $vk_config["extensions"]["PlayerResize"]["config"]["m_width"];
    if ($vk_config["extensions"]["PlayerResize"]["config"]["m_height"])
        $vk_config['player_height'] = $vk_config["extensions"]["PlayerResize"]["config"]["m_height"];
}

foreach ($CvStruct as $zid => $arr) {
    if (count($arr['items']) > 0) {
        foreach ($arr['items'] as $num => $film) {
            $CvStruct[$zid]['items'][$num]['scode'] = VideoTubes::getInstance()->getPlayer($film['scode_begin'], $film['sname']);
        }
    }
}
$CvInfo["first_code"] = VideoTubes::getInstance()->getPlayer($CvInfo["first_code_begin"], $CvInfo["first_name"]);
