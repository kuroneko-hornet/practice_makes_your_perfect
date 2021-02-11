<?php

    ini_set('display_errors', "On");
    date_default_timezone_set('Asia/Tokyo');
    
    // XSS 対策。& < > " ' などを実体参照化。
    function myesc($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
    // 新規メッセージがあれば
    if ( isset($_REQUEST['new_message']) && $_REQUEST['new_message'] != '' ){
        // 先頭30バイトを取得し、改行コードを <br> に置換
        $new_message = mb_substr($_REQUEST['new_message'], 0, 30);
        $new_message = str_replace(["\r\n", "\r", "\n"], '<br>', $new_message);

        // 新規メッセージを、配列の先頭にセット
        $buf = date("Y/m/d H:i:s") . "," . $new_message . "\n";
        $lines = [];
        array_push($lines, $buf);

        // 既存メッセージ先頭 4 件を、配列に追加
        $fp = fopen("tweets.txt", "r");
        while (( $line = fgets($fp)) !== false ) {
            array_push($lines, $line);
            if ( count($lines) > 4 ){
                break;
            }
        }
        fclose($fp);

        // 配列の内容をファイルに出力
        $fp = fopen("tweets.txt", "w");
        foreach ( $lines as $line ){
        fwrite($fp, $line);
        }
        fclose($fp);
    }

    // 日付
    // [year, month, day, hour, minute]
    $date_keys = ['year', 'month', 'day', 'hour', 'minute'];
    $today = array_combine($date_keys, explode('/', date("Y/m/d/H/i")));
    $selected_date = [
        'year'=>$_POST['view_year'] ?: $today['year'],
        'month'=>$_POST['view_month']  ?: $today['month'],
        'day'=>$_POST['view_day'] ?: $today['day']
    ];

    // 検索フォーム
    $date_list = ['year'=>range(2021, 2019, -1), 'month'=>range(1, 12), 'day'=>range(1, 31)];
    foreach(['year', 'month', 'day'] as $date_cls){
        foreach($date_list[$date_cls] as $val){
            $date_val = str_pad($val, 2, 0, STR_PAD_LEFT);
            $date_list[$date_cls] .= $date_val === $selected_date[$date_cls] ? '<option selected ' : '<option ';
            $date_list[$date_cls] .= 'value='.$date_val;
            // $date_list[$date_cls] .= $date_cls === 'day' ? '>' : '/>';
            $date_list[$date_cls] .= ">$val</option>";
        }
    }
    $view_name = [
        [" 年 ", 'view_year'],
        [' 月 ', 'view_month'],
        [' 日 ', 'view_day']
    ];

    echo '\$_POST: ';
    print_r($_POST);
    echo '<br>';
    print_r($today);
    echo '<br>';
    print_r($selected_date);
    echo '<br>';

?>

<html>
<head>
    <title>Practice Makes Your Perfect.</title>
</head>
<body>
    <h1>Practice Makes Your Perfect.</h1>
    <h2>Have you eaten anything?</h2>

    <h2>Your effort</h2>
    <form action="<?= $_SERVER["SCRIPT_NAME"]?>?<?= microtime() ?>" method="post">
        <?php foreach(['year', 'month', 'day'] as $i => $date_cls) {
            echo '<select name='.$view_name[$i][1].'>'.$date_list[$date_cls].'</select>'.$view_name[$i][0];
        } ?>
    <input type='submit' value='検索' /></form>

    <form action="<?= $_SERVER["SCRIPT_NAME"]?>?<?= microtime() ?>" method="POST">
        メッセージ: <textarea name="new_message" cols=30 rows=3></textarea>
        <input type="submit" value="投稿">
    </form>

    <?php
    $fp = fopen("tweets.txt", "r");
    while ($line = fgets($fp)) {
        $cols = explode(',', $line, 2);
        $date = $cols[0];
        $message = str_replace('<br>', "\n", $cols[1]);
        if (strpos($date, implode('/', $selected_date)) !== false) {
            echo '日時: <?= '.myesc($date).' ?><br>メッセージ: <?= '.nl2br(myesc($message)).' ?>';
        }
    }
    ?>
</body>
</html>


