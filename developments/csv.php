<?php
$row = 1;
if (($handle = fopen("employee_data_new.csv", "r")) !== FALSE) {
  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $num = count($data);
    //echo "<p> $num полей в строке $row: <br /></p>\n";
    $row++;
    echo '<h2>$data</h2>';
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    echo '<br><br><br>';
    /*for ($c=0; $c < $num; $c++) {
      echo $data[$c] . "<br />\n";
      echo $data[$c][2] . "<br />\n";
      print_r($data[$c]);
    }*/

  }
  fclose($handle);
}
?>