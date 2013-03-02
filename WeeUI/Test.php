<?php

spl_autoload_register(
        function ($class)
        {
            include "./".$class . '.php';
        });
Template::printHeaderAndDoValidation(TRUE, "Hellooo", array("templatePage.js"), 1);
?>

<p>Date: <input type='text' id='datepicker' /></p>

<div class='accordion colorbox blue'>
    <div class='status'>
        <img class='inactive' src='/RTSS/resources/images/plus-white.png'/>
        <img class='active' src='/RTSS/resources/images/minus-white.png'/>
    </div>

    <span>
        Leave Status
    </span>
</div>
<div>
    <table class='hovered'>
        <thead>
            <tr>
                <th class='hovered'>Name</th>
                <th class='hovered right'>Time CP</th>
                <th class='hovered right'>Network</th>
                <th class='hovered right'>Traffic</th>
                <th class='hovered right last'>Tiles update</th>
            </tr>
        </thead>

        <tbody>
            <tr><td>Bing</td><td class='right'>0:00:01</td><td class='right'>0,1 Mb</td><td class='right'>0 Mb</td><td class='right last'>0,1 Mb</td></tr>
            <tr><td>Internet Explorer</td><td class='right'>0:00:01</td><td class='right'>0,1 Mb</td><td class='right'>0 Mb</td><td class='right last'>0,1 Mb</td></tr>
            <tr><td>Chrome</td><td class='right'>0:00:01</td><td class='right'>0,1 Mb</td><td class='right'>0 Mb</td><td class='right last'>0,1 Mb</td></tr>
            <tr><td>News</td><td class='right'>0:00:01</td><td class='right'>0,1 Mb</td><td class='right'>0 Mb</td><td class='right last'>0,1 Mb</td></tr>
            <tr><td>Weather</td><td class='right'>0:00:01</td><td class='right'>0,1 Mb</td><td class='right'>0 Mb</td><td class='right last'>0,1 Mb</td></tr>
            <tr><td>Music</td><td class='right'>0:00:01</td><td class='right'>0,1 Mb</td><td class='right'>0 Mb</td><td class='right last'>0,1 Mb</td></tr>
        </tbody>

        <tfoot></tfoot>
    </table>
</div>
<div class='accordion colorbox green'>
    <div class='status'>
        <img class='inactive' src='/RTSS/resources/images/plus-white.png'/>
        <img class='active' src='/RTSS/resources/images/minus-white.png'/>
    </div>

    <span>
        Leave Status
    </span>
</div>
<div>
    <table class='hovered'>
        <thead>
            <tr>
                <th class='hovered'>Name</th>
                <th class='hovered right'>Time CP</th>
                <th class='hovered right'>Network</th>
                <th class='hovered right'>Traffic</th>
                <th class='hovered right last'>Tiles update</th>
            </tr>
        </thead>

        <tbody>
            <tr><td>Bing</td><td class='right'>0:00:01</td><td class='right'>0,1 Mb</td><td class='right'>0 Mb</td><td class='right last'>0,1 Mb</td></tr>
            <tr><td>Internet Explorer</td><td class='right'>0:00:01</td><td class='right'>0,1 Mb</td><td class='right'>0 Mb</td><td class='right last'>0,1 Mb</td></tr>
            <tr><td>Chrome</td><td class='right'>0:00:01</td><td class='right'>0,1 Mb</td><td class='right'>0 Mb</td><td class='right last'>0,1 Mb</td></tr>
            <tr><td>News</td><td class='right'>0:00:01</td><td class='right'>0,1 Mb</td><td class='right'>0 Mb</td><td class='right last'>0,1 Mb</td></tr>
            <tr><td>Weather</td><td class='right'>0:00:01</td><td class='right'>0,1 Mb</td><td class='right'>0 Mb</td><td class='right last'>0,1 Mb</td></tr>
            <tr><td>Music</td><td class='right'>0:00:01</td><td class='right'>0,1 Mb</td><td class='right'>0 Mb</td><td class='right last'>0,1 Mb</td></tr>
        </tbody>

        <tfoot></tfoot>
    </table>
</div>
<div class='accordion colorbox yellow'>
    <div class='status'>
        <img class='inactive' src='/RTSS/resources/images/plus-white.png'/>
        <img class='active' src='/RTSS/resources/images/minus-white.png'/>
    </div>

    <span>
        Leave Status
    </span>
</div>
<div>
    <table class='hovered'>
        <thead>
            <tr>
                <th class='hovered'>Name</th>
                <th class='hovered right'>Time CP</th>
                <th class='hovered right'>Network</th>
                <th class='hovered right'>Traffic</th>
                <th class='hovered right last'>Tiles update</th>
            </tr>
        </thead>

        <tbody>
            <tr><td>Bing</td><td class='right'>0:00:01</td><td class='right'>0,1 Mb</td><td class='right'>0 Mb</td><td class='right last'>0,1 Mb</td></tr>
            <tr><td>Internet Explorer</td><td class='right'>0:00:01</td><td class='right'>0,1 Mb</td><td class='right'>0 Mb</td><td class='right last'>0,1 Mb</td></tr>
            <tr><td>Chrome</td><td class='right'>0:00:01</td><td class='right'>0,1 Mb</td><td class='right'>0 Mb</td><td class='right last'>0,1 Mb</td></tr>
            <tr><td>News</td><td class='right'>0:00:01</td><td class='right'>0,1 Mb</td><td class='right'>0 Mb</td><td class='right last'>0,1 Mb</td></tr>
            <tr><td>Weather</td><td class='right'>0:00:01</td><td class='right'>0,1 Mb</td><td class='right'>0 Mb</td><td class='right last'>0,1 Mb</td></tr>
            <tr><td>Music</td><td class='right'>0:00:01</td><td class='right'>0,1 Mb</td><td class='right'>0 Mb</td><td class='right last'>0,1 Mb</td></tr>
        </tbody>

        <tfoot></tfoot>
    </table>

    <?php
    Template::printFooter();
    ?>
