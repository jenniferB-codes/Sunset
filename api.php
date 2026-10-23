<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
ini_set('display_errors', '0');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ini_set('session.cookie_secure', '1');
session_start();
function reply(array $data, int $code = 200): never { http_response_code($code); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') reply(['ok'=>false,'error'=>'Método no permitido'],405);
$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) $input = [];
try {
    $config = require __DIR__ . '/config.php';
    $db = new PDO('mysql:host='.$config['host'].';dbname='.$config['database'].';charset=utf8mb4', $config['username'], $config['password'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false]);
    if ($action === 'register') {
        $name = trim((string)($input['name'] ?? '')); $email = strtolower(trim((string)($input['email'] ?? ''))); $pass = (string)($input['password'] ?? '');
        if (mb_strlen($name)<2 || mb_strlen($name)>100 || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($pass)<8 || strlen($pass)>128) reply(['ok'=>false,'error'=>'Revisa el nombre, correo y contraseña (mínimo 8 caracteres).'],422);
        $stmt=$db->prepare('INSERT INTO users (name,email,password_hash) VALUES (?,?,?)');
        try { $stmt->execute([$name,$email,password_hash($pass,PASSWORD_DEFAULT)]); } catch(PDOException $e) { if ($e->getCode()==='23000') reply(['ok'=>false,'error'=>'Ese correo ya está registrado.'],409); throw $e; }
        session_regenerate_id(true); $_SESSION['user_id']=(int)$db->lastInsertId(); reply(['ok'=>true]);
    }
    if ($action === 'login') {
        $stmt=$db->prepare('SELECT id,password_hash FROM users WHERE email=?');$stmt->execute([strtolower(trim((string)($input['email']??'')))]);$user=$stmt->fetch();
        if (!$user || !password_verify((string)($input['password']??''),$user['password_hash'])) reply(['ok'=>false,'error'=>'Correo o contraseña incorrectos.'],401);
        session_regenerate_id(true);$_SESSION['user_id']=(int)$user['id'];reply(['ok'=>true]);
    }
    if ($action === 'logout') { $_SESSION=[];session_destroy();reply(['ok'=>true]); }
    $id=(int)($_SESSION['user_id']??0);
    if ($action === 'me') {
        if (!$id) reply(['ok'=>true,'user'=>null]);
        $stmt=$db->prepare('SELECT id,name,email FROM users WHERE id=?');$stmt->execute([$id]);reply(['ok'=>true,'user'=>$stmt->fetch()?:null]);
    }
    if (!$id) reply(['ok'=>false,'error'=>'Inicia sesión para guardar y consultar tus solicitudes.'],401);
    if ($action === 'save_booking') {
        $products=['simple-picnic'=>['The Simple Picnic',24],'classic-picnic'=>['The Classic Picnic',42],'luxury-picnic'=>['The Luxury Picnic',78],'simple-brunch'=>['The Simple Brunch',28],'classic-brunch'=>['The Classic Brunch',48],'luxury-brunch'=>['The Luxury Brunch',95]];
        $activities=['none'=>['Sin actividad',0],'paint'=>['Paint & Picnic',12],'mug'=>['Paint Your Mug',15],'cupcakes'=>['Decorate & Create',18],'tote'=>['Create Your Tote',16],'bracelets'=>['Friendship Bracelets',9],'letters'=>['Write & Keep',7]];
        $extrasCatalog=['flowers'=>['Flores',20],'card'=>['Tarjeta personalizada',5],'photos'=>['Kit de fotos',15],'cake'=>['Mini cake',25],'strawberries'=>['Fresas con chocolate',18],'drink'=>['Bebida especial',8]];
        $extraIds=$input['extra_ids']??[];
        if (!is_array($extraIds) || count($extraIds)>count($extrasCatalog) || count($extraIds)!==count(array_unique($extraIds))) reply(['ok'=>false,'error'=>'Extras no válidos.'],422);
        $extraNames=[];$extraTotal=0;
        foreach ($extraIds as $extraId) { if (!is_string($extraId) || !isset($extrasCatalog[$extraId])) reply(['ok'=>false,'error'=>'Extra no válido.'],422); $extraNames[]=$extrasCatalog[$extraId][0];$extraTotal+=$extrasCatalog[$extraId][1]; }
        $extrasSummary=$extraNames?implode(', ',$extraNames):'Ninguno';
        $p=$products[$input['product_id']??'']??null;$a=$activities[$input['activity_id']??'']??null;$people=filter_var($input['people']??null,FILTER_VALIDATE_INT);$date=(string)($input['date']??'');$comments=trim((string)($input['comments']??''));
        $validDate=DateTimeImmutable::createFromFormat('!Y-m-d',$date);
        if (!$p || !$a || $people===false || $people<2 || $people>20 || !$validDate || $validDate->format('Y-m-d')!==$date || $date<(new DateTimeImmutable('today'))->format('Y-m-d') || mb_strlen($comments)>500) reply(['ok'=>false,'error'=>'Comprueba el paquete, la actividad, la fecha y el número de personas.'],422);
        $stmt=$db->prepare('INSERT INTO bookings (user_id,product_name,product_price,activity_name,activity_price,people,event_date,comments,total,extras_summary,extras_total) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([$id,$p[0],$p[1],$a[0],$a[1],$people,$date,$comments,($p[1]+$a[1])*$people+$extraTotal,$extrasSummary,$extraTotal]);reply(['ok'=>true,'booking_id'=>(int)$db->lastInsertId()]);
    }
    if ($action === 'list_bookings') {
        $stmt=$db->prepare('SELECT id,product_name,product_price,activity_name,activity_price,people,event_date,total,extras_summary,extras_total FROM bookings WHERE user_id=? ORDER BY id DESC');$stmt->execute([$id]);reply(['ok'=>true,'bookings'=>$stmt->fetchAll()]);
    }
    reply(['ok'=>false,'error'=>'Acción no reconocida'],404);
} catch(Throwable $e) { error_log($e->getMessage());reply(['ok'=>false,'error'=>'Error del servidor. Revisa la configuración de PHP y MySQL.'],500); }
