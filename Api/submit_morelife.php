<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

error_log("MoreLife API hit");

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception("Invalid JSON");
    }

    $required = [
        'full_name','email','phone','location','age','education',
        'cause','duration','medication','start_month',
        'session_type','session_price','payment_method'
    ];

    $missing = [];
    foreach ($required as $f) {
        if (!isset($input[$f]) || $input[$f] === '') {
            $missing[] = $f;
        }
    }

    if (empty($input['challenges']) || !is_array($input['challenges'])) {
        $missing[] = "challenges";
    }

    if ($missing) {
        http_response_code(400);
        echo json_encode(["success"=>false,"message"=>"Missing: ".implode(', ', $missing)]);
        exit();
    }

    require_once __DIR__ . '/config/database.php';
    $db = (new Database())->getConnection();

    $reference = "MORELIFE-" . time();

    $stmt = $db->prepare("
        INSERT INTO morelife_sessions
        (reference, full_name, email, phone, location, age, education_level,
         challenges, other_challenge, challenge_cause, challenge_duration,
         trigger_incident, on_medication, start_month, session_type,
         session_price, payment_method, status, created_at)
        VALUES
        (:reference, :full_name, :email, :phone, :location, :age, :education_level,
         :challenges, :other_challenge, :challenge_cause, :challenge_duration,
         :trigger_incident, :on_medication, :start_month, :session_type,
         :session_price, :payment_method, 'pending', NOW())
    ");

    $stmt->execute([
        ":reference"        => $reference,
        ":full_name"        => $input["full_name"],
        ":email"            => $input["email"],
        ":phone"            => $input["phone"],
        ":location"         => $input["location"],
        ":age"              => $input["age"],
        ":education_level"  => $input["education"],
        ":challenges"       => json_encode($input["challenges"]),
        ":other_challenge"  => $input["other_challenge"] ?? null,
        ":challenge_cause"  => $input["cause"],
        ":challenge_duration" => $input["duration"],
        ":trigger_incident" => $input["trigger_incident"] ?? null,
        ":on_medication"    => $input["medication"],
        ":start_month"      => $input["start_month"],
        ":session_type"     => $input["session_type"],
        ":session_price"    => $input["session_price"],
        ":payment_method"   => $input["payment_method"]
    ]);

    echo json_encode([
        "success" => true,
        "reference" => $reference,
        "message" => "Application stored"
    ]);

} catch (Exception $e) {
    error_log("MoreLife Error: ".$e->getMessage());
    http_response_code(500);
    echo json_encode(["success"=>false,"message"=>$e->getMessage()]);
}
?>
