<div id="topbar">
    <div class="fltrt"><?php echo $_SESSION['accname']; ?> | <a href="/RTSS/_logout.php">Log out</a></div>
    <ul class="breadcrumb">
        <?php 
            foreach ($TOPBAR_LIST as $tab) {
                $url=$tab['url'];
                if ($url) $content="<a href=\"$url\">{$tab['tabname']}</a>";
                else $content=$tab['tabname'];
                echo <<< EOD
                    <li>$content</li>
EOD;
            }
        ?>
    </ul>
</div>
