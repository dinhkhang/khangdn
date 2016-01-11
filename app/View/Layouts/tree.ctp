<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
$cakeDescription = __d('page_meta_title', Configure::read('sysconfig.App.name'));
?>
<!DOCTYPE html>
<html>
    <head>
        <?php echo $this->Html->charset(); ?>
        <title>
            <?php echo $cakeDescription ?>:
            <?php echo $this->fetch('title'); ?>
        </title>
        <?php
        echo $this->Html->meta('icon');

//		echo $this->Html->css('cake.generic');

        echo $this->fetch('meta');

        echo $this->Html->css('bootstrap.min');
        echo $this->Html->css('/font-awesome/css/font-awesome.min');
        echo $this->Html->css('animate');
        echo $this->Html->css('style');
        echo $this->Html->css('main');
        echo $this->fetch('css');

        // Mainly scripts
        echo $this->Html->script('jquery-2.1.1');
        echo $this->Html->script('bootstrap.min');
//        echo $this->Html->script('plugins/metisMenu/jquery.metisMenu');
//        echo $this->Html->script('plugins/slimscroll/jquery.slimscroll.min');

        echo $this->Html->script('plugins/nestable/jquery.nestable');
        echo $this->fetch('script');

        // Custom and plugin javascript
//        echo $this->Html->script('inspinia');
//        echo $this->Html->script('plugins/pace/pace.min');
        ?>
    </head>
    <body>

        <?php echo $this->fetch('content'); ?>

    </body>
</html>
