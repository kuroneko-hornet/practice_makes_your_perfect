<?php
  // http://x68000.q-e-d.net/~68user/cloud/tuto-aws-mini-twitter.html で使用する
  // サンプルアプリケーション。

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

  // 表示画面用
  $view_year = $_POST['view_year'];
  $view_month = $_POST['view_month'];
  $view_day = $_POST['view_day'];

  $view_date = [
	  ['2020/'=>'2020', '2021/'=>'2021'],
	  ['01/'=>'1', '02/'=>2, '03/'=>'3'],
	  ['08'=>'8', '09'=>'9']];  
  for ($i=0; $i<3; $i++){
    foreach($view_date[$i] as $key => $val){
      $view_date[$i] .= "<option value='". $key;
      $view_date[$i] .= "'>". $val. "</option>";
    }}

  print_r($_POST);

?>
<html>
<head>
  <title>Practice Makes Your Perfect.</title>
</head>
<body>
  <h1>Practice Makes Your Perfect.</h1>
  <h2>Have you eaten anything?</h2>
    <select name='age'>
    <option value='young'>10代～20代</option>
    <option value='middle'>30代～50代</option>
    <option value='senior'>60代以上</option>
    </select>

  <h2>Your effort</h2>
  <form action="<?= $_SERVER["SCRIPT_NAME"]?>?<?= microtime() ?>" method="post">
    年: <select name='view_year'>
      <?php
      echo $view_date[0]; ?>
    </select>
    月: <select name='view_month'>
      <?php
      echo $view_date[1]; ?>
    </select>
    日: <select name='view_day'>
      <?php
      echo $view_date[2]; ?>
    </select>
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
    print($view_year.$view_month.$view_day);
    if (strpos($date, $view_year.$view_month.$view_day) !== false) {
  ?>
      <p>
        日時: <?= myesc($date) ?><br>
        メッセージ: <?= nl2br(myesc($message)) ?>
      </p>
  <?php
  }}
  ?>
</body>
</html>


