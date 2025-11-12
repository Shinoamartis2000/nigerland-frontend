[file name]: Api/test_json.php
[file content begin]
<?php
header('Content-Type: application/json');
echo json_encode(["message" => "JSON is working!", "status" => "success"]);
?>
[file content end]