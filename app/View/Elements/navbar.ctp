<?php
$user = CakeSession::read('Auth.User');
//$player = CakeSession::read('Auth.Player');
?>
<nav class="light-blue lighten-1" role="navigation">
    <div class="nav-wrapper container">
        <a id="logo-container" href="<?php echo Router::url(array('controller' => 'Home', 'action' => 'index')) ?>" class="brand-logo">Game Quiz</a>
        <ul class="right hide-on-med-and-down">
            <?php if (empty($user)): ?>
                <li>
                    <a href="<?php echo Router::url(array('controller' => 'Visitors', 'action' => 'login')) ?>" class="waves-effect waves-light ">
                        Đăng nhập wifi
                    </a>
                </li>
            <?php endif; ?>
            <?php if (!empty($user)): ?>
                <li>
                    <a href="<?php echo Router::url(array('controller' => 'Packages', 'action' => 'about')) ?>" class="waves-effect waves-light ">
                        Tài khoản
                    </a>
                </li>
            <?php endif; ?>
            <li><a href="<?php echo Router::url(array('controller' => 'Visitors', 'action' => 'logout')) ?>" class="waves-effect waves-light ">Đăng xuất</a></li>
    <!--<li><a href="<?php // echo Router::url(array('controller' => 'Packages', 'action' => 'view'))      ?>" class="waves-effect waves-light ">Các gói dịch vụ</a></li>-->
        </ul>     
        <a href="<?php echo Router::url(array('controller' => 'Home', 'action' => 'index')) ?>" class="button-collapse-as button-back"><i class="material-icons">home</i></a>
        <a href="#" data-activates="nav-mobile" class="button-collapse right-nav"><i class="material-icons">menu</i></a>
        <ul id="nav-mobile" class="side-nav side-bar-add">
            <?php if (empty($user)): ?>
                <li>
                    <a href="<?php echo Router::url(array('controller' => 'Visitors', 'action' => 'login')) ?>" class="waves-effect waves-light ">
                        Đăng nhập wifi
                    </a>
                </li>
            <?php endif; ?>
            <?php if (!empty($user)): ?>
                <li>
                    <a href="<?php echo Router::url(array('controller' => 'Packages', 'action' => 'about')) ?>" class="waves-effect waves-light ">
                        Tài khoản
                    </a>
                </li>
            <?php endif; ?>
            <li><a href="<?php echo Router::url(array('controller' => 'Visitors', 'action' => 'logout')) ?>" class="waves-effect waves-light ">Đăng xuất</a></li>
<!--<li><a href="<?php // echo Router::url(array('controller' => 'Packages', 'action' => 'view'))      ?>" class="waves-effect waves-light ">Các gói dịch vụ</a></li>-->
        </ul>
    </div>
</nav>