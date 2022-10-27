<?php

declare(strict_types = 1);

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$root = dirname(__DIR__) . DIRECTORY_SEPARATOR;

define('APP_PATH', $root . 'app' . DIRECTORY_SEPARATOR);
define('VIEWS_PATH', $root . 'views' . DIRECTORY_SEPARATOR);


require_once  APP_PATH . 'config.php';

$sql_users = 'SELECT * FROM users;';



$query = "SELECT a.created_at AS published, d.id, j.id AS project_id, j.user_id, d.customer_id, d.delivery_date, d.updated_at, d.type, (j.cost_location_id * 1) AS cost_location_id,
    CASE price_unit
        WHEN 'pro Stück' THEN (p.amount * p.buying_price * 1)
        WHEN 'pro 1.000 Stück' THEN (p.amount / 1000 * p.buying_price)
        ELSE p.buying_price * 1 END AS buying_price,
    CASE price_unit 
        WHEN 'pro Stück' THEN (p.amount * p.retail_price * 1)
        WHEN 'pro 1.000 Stück' THEN (p.amount / 1000 * p.retail_price)
        ELSE p.retail_price * 1 END AS retail_price
    FROM activities a JOIN documents d ON d.id = a.activityable_id 
    JOIN products p ON d.id = p.document_id 
    JOIN projects j ON d.project_id = j.id 
    JOIN users u ON j.user_id = u.id 
    WHERE a.action = 'customer-confirmation.published'
    AND d.type = 'customer-confirmation'
    AND d.status IN ('published', 'invoiced')";

$published_from = date('y-m-d', strtotime('01.01.2021'));
$published_to = date('y-m-d', strtotime('31.12.2021'));
$delivery_date_from = date('y-m-d', strtotime('01.01.2021'));
$delivery_date_to = date('y-m-d', strtotime('31.12.2021'));

if ($published_from) {
    $query = sprintf($query . " AND a.created_at >= '%s 00:00:00' ", $published_from);
}
if ($published_to) {
    $query = sprintf($query . " AND a.created_at <= '%s 23:59:59' ", $published_to);
}
if ($delivery_date_from) {
    $query = sprintf($query . " AND d.delivery_date >= '%s 00:00:00' ", $delivery_date_from);
}
if ($delivery_date_to) {
    $query = sprintf($query . " AND d.delivery_date <= '%s 23:59:59' ", $delivery_date_to);
}

$query2 = "SELECT SUM(COALESCE(retail_price,0)) AS VK, 
SUM(COALESCE(buying_price,0)) AS EK,
SUM(COALESCE(retail_price,0) - COALESCE(buying_price,0)) AS Marge,
MONTHNAME(DATE(published)) AS Monat,
COUNT(DISTINCT id) AS Produkte
FROM (
    $query
) AS T
GROUP BY Monat, MONTH(DATE(published))
ORDER BY MONTH(DATE(published));";

$query3 = "SELECT SUM(COALESCE(retail_price,0)) AS VK, 
SUM(COALESCE(buying_price,0)) AS EK,
SUM(COALESCE(retail_price,0) - COALESCE(buying_price,0)) AS Marge,
MONTHNAME(DATE(published)) AS Monat,
COUNT(DISTINCT id) AS Produkte_insgesamt
FROM (
SELECT a.created_at AS published, d.id, j.id AS project_id, j.user_id, d.customer_id, d.delivery_date, d.updated_at, d.type, (j.cost_location_id * 1) AS cost_location_id,
    CASE price_unit WHEN 'pro Stück' THEN (p.amount * p.buying_price * 1)
        WHEN 'pro 1.000 Stück' THEN (p.amount / 1000 * p.buying_price)
        ELSE p.buying_price * 1 END AS buying_price,
    CASE price_unit WHEN 'pro Stück' THEN (p.amount * p.retail_price * 1)
        WHEN 'pro 1.000 Stück' THEN (p.amount / 1000 * p.retail_price)
        ELSE p.retail_price * 1 END AS retail_price
FROM activities a JOIN documents d ON d.id = a.activityable_id 
    JOIN products p ON d.id = p.document_id 
    JOIN projects j ON d.project_id = j.id 
    JOIN users u ON j.user_id = u.id 
WHERE YEAR(a.created_at) = YEAR(CURDATE()) - 1 
    AND a.action = 'customer-confirmation.published'
    AND d.type = 'customer-confirmation'
    AND d.status IN ('published', 'invoiced')
) AS T
GROUP BY Monat, MONTH(DATE(published))
ORDER BY MONTH(DATE(published));";


$queryResult = $db->query($query3)->fetchAll();

//header('Content-Type: application/json; charset=utf-8');

$json = json_encode($queryResult);
if ($json === false) {
    // Avoid echo of empty string (which is invalid JSON), and
    // JSONify the error message instead:
    $json = json_encode(["jsonError" => json_last_error_msg()]);
    if ($json === false) {
        // This should not happen, but we go all the way now:
        $json = '{"jsonError":"unknown"}';
    }
    // Set HTTP response status code to: 500 - Internal Server Error
    http_response_code(500);
}

echo $json;






