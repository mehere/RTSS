<div id="topbar">
    <div class="fltrt"><?php echo $_SESSION['accname']; ?> | <a href="/RTSS/_logout.php">Log out</a></div>
    <ul class="breadcrumb">
        <?php 
            foreach ($TOPBAR_LIST as $tab) {
                $url=$tab['url'];
                if ($url) $url="href=\"$url\"";
                echo <<< EOD
                    <li><a $url>{$tab['tabname']}</a></li>
EOD;
            }
        ?>
    </ul>
</div>
