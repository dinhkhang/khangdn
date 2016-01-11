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
?>  
<!DOCTYPE html>
<html lang="en">
    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php
        echo $this->fetch('meta');
        ?>
        <title>Halo! VietNam</title>
        <link href="<?php echo Router::url('/') ?>css/assets/css/bootstrap.min.css" rel="stylesheet">
        <link href="<?php echo Router::url('/') ?>css/assets/css/font-awesome.min.css" rel="stylesheet">
        <link href="<?php echo Router::url('/') ?>css/assets/css/swiper.min.css" rel="stylesheet">
        <link href="<?php echo Router::url('/') ?>css/assets/css/jquery-ui.css" rel="stylesheet">
        <link href="<?php echo Router::url('/') ?>css/assets/css/normalize.css" rel="stylesheet">
        <link href="<?php echo Router::url('/') ?>css/assets/css/owl.carousel.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:400,500,700,300">
        <link href="<?php echo Router::url('/') ?>css/assets/css/custom.css" rel="stylesheet">
        <link href="<?php echo Router::url('/') ?>css/assets/css/reponsive.css" rel="stylesheet">
        <?php
        echo $this->fetch('css');
        ?>
        <script src="<?php echo Router::url('/') ?>css/assets/js/offline.min.js"></script>
        <script src="<?php echo Router::url('/') ?>css/assets/js/jquery-1.11.3.min.js"></script>
        <script src="<?php echo Router::url('/') ?>css/assets/js/fastclick.js"></script>
        <script src="<?php echo Router::url('/') ?>css/assets/js/jquery.quickfit.js"></script>
        <script src="<?php echo Router::url('/') ?>css/assets/js/bootstrap.min.js"></script>
        <script src="<?php echo Router::url('/') ?>css/assets/js/localforage.js"></script>
        <script src="<?php echo Router::url('/') ?>css/assets/js/swiper.min.js"></script>
        <script src="<?php echo Router::url('/') ?>css/assets/js/jquery-ui.js"></script>
        <script src="<?php echo Router::url('/') ?>css/assets/js/sidebar.js"></script>
        <script src="<?php echo Router::url('/') ?>css/assets/js/owl.carousel.js"></script>
        <script src="<?php echo Router::url('/') ?>css/assets/js/OwlCarousel2Thumbs.min.js"></script>
        <script src="<?php echo Router::url('/') ?>css/assets/js/custom.js"></script>
        <script src="<?php echo Router::url('/') ?>css/assets/js/readmore.js"></script>

        <?php
        echo $this->element('js/main');
        ?>
        <?php
        echo $this->fetch('script');
        ?>
    </head>
    <body>
        <?php
        $this->startIfEmpty('header');
        echo $this->element('Home/header');
        $this->end();
        echo $this->fetch('header');
        ?> 
        <?php
        echo $this->fetch('recent_history');
        ?>
        <?php echo $this->fetch('content'); ?>
        <?php
//        $this->startIfEmpty('footer');
//        echo $this->element('Home/footer');
//        $this->end();
//        echo $this->fetch('footer');
        ?>
        <?php
        $this->startIfEmpty('alert_modal');
        echo $this->element('alert_modal');
        $this->end();
        echo $this->fetch('alert_modal');
        ?> 
        <div class="overlay-sidebar hide"></div>
        <div class="overl hide"></div>
    </body>
</html>
