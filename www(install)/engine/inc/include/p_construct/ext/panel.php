<?php

if (!vkv_check_access("ext")) { exit("Module VideoConstructor - access denied!"); }

include_once (ENGINE_DIR . '/inc/include/p_construct/classes/VcExtensionForm.php');

$id = $_GET['id'];
//print_r($vk_config['extensions']);
if (isset($vk_config["extensions"][$id])) {
    $form = new VcExtensionForm($id);
    $status = false;
    if (isset($_POST['submit'])) {
        $status = $form->save();
    }
    ?>
<style type="text/css">
    .extconf {padding: 15px;}
    .extconf label {font-weight: bold; font-size: 14px;}
    .extconf input, .extconf select, .extconf textarea {width: 400px;}
</style>
<div class="extconf">
    <h3>Настройка: <?php echo $id; ?></h3><br>
    <?php
        if ($status!==false) {
            if ($status===true)
                $status = 'Настройки успешно сохранены!';
            ?>
    <div style="padding:10px;margin:10px auto 10px;width:500px;border:1px solid black;background-color: #d0d0d0; border-radius:5px;">
                <?php echo strip_tags($status);?>
            </div>
    <?php
        }
    ?>
    <form action="" method="POST">
        <?php echo $form->compuile(); ?>
        <input name="submit" type="submit" value="Сохранить" style="width:130px;">
    </form>
</div>
<?php
} else {
    ?>
<div style="padding:10px;margin:10px auto 10px;width:500px;border:1px solid #d53300;background-color: #f2d2c8; border-radius:5px;">
    Расширение не найдено.
</div>
    <?php
}
