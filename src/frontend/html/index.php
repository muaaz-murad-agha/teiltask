<?php
// input reqeust variabls declare
$anfang_1 = $_POST['published_from_1'];
$end_1    = $_POST['published_to_1'];
$anfang_2 = $_POST['published_from_2'];
$end_2    = $_POST['published_to_2'];
$anfang_3 = $_POST['published_from_3'];
$end_3    = $_POST['published_to_3'];

// interval as day by day for first priode
$date1=date_create($anfang_1);
$date2=date_create($end_1);
$diff=date_diff($date1,$date2);

// the nummber of days as intrval
// $interval =(int)$diff->format("%a");

//intrval from subject buliding
$interval = new DateInterval('P1D');
$period = new DatePeriod($date1, $interval, $date2);

// By iterating over the DatePeriod object, all of the
// recurring dates within that period are printed.
$data =[];
foreach ($period as $date) {
    $label=$date->format('Y-m-d')."<br>";
    $priodArr[]=$label;
    $data['labels'][]=$label;
    $data['amount'][]=0;
    $data['sales'][]=0;
    $data['marge'][]=0;
}
// we get the data from query from certain period
// wir holen die data aus Query aus bestimmter Priode
$dataInOurPriode = array_values(array_filter($entries,
    function ($entry) {
        $localdate=($entry->published)->format('Y-m-d');
        if ($localdate >= $date1->format('Y-m-d') || $localdate <= $date2->format('Y-m-d')   ){

            return $localdate;
        }
    }
));

// here we insert both arrays in one and feel all data on it
// hier fügen beide Arrays in einer ein und fühlen wir alle Data auf
foreach ($dataInOurPriode as $entry) {
    $ds = $entry->date;
    $datetime = DateTime::createFromFormat('Y-m-d', $ds);
    $index = (int)$datetime->format('d');
    $data['amount'][$index - 1] = $entry->amount;
    $data['sales'][$index - 1] = $entry->retail_price;
    $data['marge'][$index - 1] = $entry->marge;
}

// Here we add the value of a field onto its sucessor such that
// the n-th element is the sum of the first up to the n-th element

for ($i = 1; $i < count($data['labels']); $i++) {
    $data['amount'][$i] += $data['amount'][$i - 1];
    $data['sales'][$i] += $data['sales'][$i - 1]; 
    $data['marge'][$i] += $data['marge'][$i - 1];
}


// At the end we round all the fields that contain floating point values
for ($i = 0; $i < count($data['labels']); $i++) {
    $data['sales'][$i] = round($data['sales'][$i], 2);
    $data['marge'][$i] = round($data['marge'][$i], 2);
}

//SQL Qurey 
use App\Request;


$userQuery = "SELECT ROUND(SUM(COALESCE(retail_price,0)), 2) AS retail_price,
ROUND(SUM(COALESCE(buying_price,0)), 2) AS buying_price,
SUM(COALESCE(retail_price,0) - COALESCE(buying_price,0)) AS marge,
COUNT(DISTINCT id) AS amount,
DATE_FORMAT(published, '%Y') AS year,
DATE(published) AS date

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
WHERE a.action = 'customer-confirmation.published'
AND d.type = 'customer-confirmation'
AND d.status IN ('published', 'invoiced')

AND DATE(a.created_at)) >= Date($anfang_1)
) AS T
GROUP BY year, date
ORDER BY year;";

$entries = $db->query($userQuery)->fetchAll();
?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="utf8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="stylesheet.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js" integrity="sha256-ErZ09KkZnzjpqcane4SCyyHsKAXMvID9/xwbl/Aq1pc=" crossorigin="anonymous"></script>
        <title> Daynamuische Seite</title>
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="index.html">
                    <img src="https://fabs.de/media/image/layout/logo.png" alt="FABS Logo" width="60" height="30" class="d-inline-block align-text-top">
                    Dashboard
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                   
            </div>
        </nav>

        <div class="container overflow-hidden">
            <form id="chartForm" method = 'POST'>
                <div class="row d-flex justify-content-center g-2">
                    <div class="col col-sm-3">
                        <label for="published_from" class="col-form-label">Veröffentlicht von</label>
                        <input type="date" class="form-control" id="published_from_1" name="published_from_1" value="2021-01-01">
                    </div>
                    <div class="col col-sm-3">
                        <label for="published_to" class="col-form-label">Veröffentlicht bis</label>
                        <input type="date" class="form-control" id="published_to_1" name="published_to_1" value="2021-12-31">
                    </div>

                    <div class="w-100"></div>

                    <div class="col col-sm-3">
                        <label for="published_from" class="col-form-label">Veröffentlicht von</label>
                        <input type="date" class="form-control" id="published_from_2" name="published_from_2" value="2021-01-01">
                    </div>
                    <div class="col col-sm-3">
                        <label for="published_to" class="col-form-label">Veröffentlicht bis</label>
                        <input type="date" class="form-control" id="published_to_2" name="published_to_2" value="2021-12-31">
                    </div>

                    <div class="w-100"></div>

                    <div class="col col-sm-3">
                        <label for="published_from" class="col-form-label">Veröffentlicht von</label>
                        <input type="date" class="form-control" id="published_from_3" name="published_from_3" value="2021-01-01">
                    </div>
                    <div class="col col-sm-3">
                        <label for="published_to" class="col-form-label">Veröffentlicht bis</label>
                        <input type="date" class="form-control" id="published_to_3" name="published_to_3" value="2021-12-31">
                    </div>

                    <div class="w-100"></div>

                    <div class="col text-center">
                        <input class="btn btn-primary" type="submit" id="update">
                    </div>
                </div>
            </form>
        </div>
        <div class="container">
            <canvas id="myChart"></canvas>
        </div>
        <div class="container">
            <pre id="jsonString"></pre>
        </div>
        <script src="../js/daynamic.js"></script>
        <!-- <script src="../old/employee.js"></script> -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    </body>
</html>