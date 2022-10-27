<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="utf8">
        <title>Simple Form</title>
    </head>
    <body>
        <div>
            <form method="post" action="../app/chartdata.php">
                <label for="published_from">Veröffentlicht von</label>
                <input type="date" id="published_from" name="published_from" value="2020-06-01"></br>
                <label for="published_to">Veröffentlicht bis</label>
                <input type="date" id="published_to" name="published_to"value="2021-12-31"></br>
                <label for="delivery_date_from">Liefertermin von</label>
                <input type="date" id="delivery_date_from" name="delivery_date_from" value="2020-06-01"></br>
                <label for="delivery_date_to">Liefertermin von</label>
                <input type="date" id="delivery_date_to" name="delivery_date_to" value="2021-12-31"></br>
                <input type="submit">
            </form>
    </body>
</html>