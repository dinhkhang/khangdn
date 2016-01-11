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
<?php $base = $this->request->base; ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0"/>
        <title>Game Quiz</title> 

        <!-- CSS  -->
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="<?php echo Router::url('/') ?>css/gamequiz/css/materialize.css" type="text/css" rel="stylesheet" media="screen,projection"/>
        <link href="<?php echo Router::url('/') ?>css/gamequiz/css/style.css" type="text/css" rel="stylesheet" media="screen,projection"/> 
        <!--  Scripts-->
        <script src="<?php echo Router::url('/') ?>css/gamequiz/js/jquery-2.1.1.min.js"></script>		
        <script src="<?php echo Router::url('/') ?>css/gamequiz/js/materialize.js"></script>
        <script src="<?php echo Router::url('/') ?>css/gamequiz/js/init.js"></script>	
    </head>
    <body>
        <div class="wrap-content">
            <!--nav-->
            <?php echo $this->element('navbar'); ?>
            <!--#nav-->
            <!--tab-->
            <div class="">
                <div class="col s12">
                    <?php
                    $play_active = $rule_active = $rating_active = $score_active = '';
                    switch ($this->here) {

                        case $this->webroot . 'gamequiz':
                            $play_active = 'active';
                            break;
                        case $this->webroot . 'rules':
                            $rule_active = 'active';
                            break;
                        case $this->webroot . 'GameRatings':
                            $rating_active = 'active';
                            break;
                        case $this->webroot . 'CheckScores':
                            $score_active = 'active';
                            break;
                    }
                    ?>
                    <ul class="tabsx add-tab">
                        <li class="tabx col s3"><a class="<?php echo $play_active ?>" href="<?php echo $base; ?>/gamequiz">Chơi game</a></li>
                        <li class="tabx col s3"><a class="<?php echo $rule_active ?>" href="<?php echo $base; ?>/rules">Thể lệ</a></li>		
                        <li class="tabx col s3"><a class="<?php echo $rating_active ?>" href="<?php echo $base; ?>/GameRatings">Xếp hạng</a></li>
                        <li class="tabx col s3"><a class="<?php echo $score_active ?>" href="<?php echo $base; ?>/CheckScores">Tra điểm</a></li>
                    </ul>
                </div>
                <?php echo $this->fetch('content'); ?>
            </div>
            <!--#tab-->  
        </div>
    </body>
</html>
