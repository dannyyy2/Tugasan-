<?php
$voltage = $_POST['voltage'] ?? '';
$current = $_POST['current'] ?? '';
$rate = $_POST['rate'] ?? '';
$errors = [];
$results = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($voltage === '' || !is_numeric($voltage) || (float) $voltage < 0) {
        $errors[] = 'Voltage must be a valid non-negative number.';
    }

    if ($current === '' || !is_numeric($current) || (float) $current < 0) {
        $errors[] = 'Current must be a valid non-negative number.';
    }

    if ($rate === '' || !is_numeric($rate) || (float) $rate < 0) {
        $errors[] = 'Current rate must be a valid non-negative number.';
    }

    if (!$errors) {
        $voltageValue = (float) $voltage;
        $currentValue = (float) $current;
        $rateValue = (float) $rate;

        $powerWatts = $voltageValue * $currentValue;
        $powerKilowatts = $powerWatts / 1000;
        $energyPerHour = $powerKilowatts;
        $energyPerDay = $energyPerHour * 24;
        $totalPerHour = $energyPerHour * ($rateValue / 100);
        $totalPerDay = $energyPerDay * ($rateValue / 100);
        $rateInRm = $rateValue / 100;
        $tableRows = [];

        for ($hour = 1; $hour <= 24; $hour++) {
            $tableRows[] = [
                'hour' => $hour,
                'energy' => $energyPerHour * $hour,
                'total' => $totalPerHour * $hour,
            ];
        }

        $results = [
            'powerWatts' => $powerWatts,
            'powerKilowatts' => $powerKilowatts,
            'energyPerHour' => $energyPerHour,
            'energyPerDay' => $energyPerDay,
            'totalPerHour' => $totalPerHour,
            'totalPerDay' => $totalPerDay,
            'rateInRm' => $rateInRm,
            'tableRows' => $tableRows,
        ];
    }
}

function displayValue($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function formatNumber($value)
{
    return number_format((float) $value, 4);
}

function formatTableNumber($value, $decimals = 5)
{
    $formatted = number_format((float) $value, $decimals, '.', '');
    return rtrim(rtrim($formatted, '0'), '.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Electricity Calculator</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .calculator-card {
            border: 0;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        .result-box {
            background-color: #ffffff;
            border-left: 4px solid #007bff;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .formula-box {
            background-color: #e9f2ff;
            border-radius: 8px;
            padding: 16px;
        }

        .summary-box {
            background-color: #ffffff;
            border: 1px solid #b8d6ff;
            border-radius: 8px;
            padding: 16px;
            margin-top: 24px;
            margin-bottom: 24px;
        }

        .summary-box p {
            color: #003f88;
            font-weight: 700;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        .summary-box p:last-child {
            margin-bottom: 0;
        }

        .table thead th {
            border-top: 0;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card calculator-card">
                    <div class="card-body p-4 p-md-5">
                        <h1 class="h3 mb-3">Electricity Consumption Calculator</h1>
                        <p class="text-muted">Calculate power, energy usage, and total electricity charge up to 24 hours.</p>

                        <div class="formula-box mb-4">
                            <h2 class="h5">Formulas Used</h2>
                            <ul class="mb-0">
                                <li>Power (W) = Voltage (V) x Current (A)</li>
                                <li>Energy per Hour (kWh) = Power (W) / 1000</li>
                                <li>Energy per Day (kWh) = Energy per Hour x 24</li>
                                <li>Total Charge = Energy (kWh) x (Current Rate / 100)</li>
                            </ul>
                        </div>

                        <?php if ($errors): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo displayValue($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="">
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="voltage">Voltage (V)</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        id="voltage"
                                        name="voltage"
                                        step="any"
                                        min="0"
                                        value="<?php echo displayValue($voltage); ?>"
                                        required
                                    >
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="current">Current (A)</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        id="current"
                                        name="current"
                                        step="any"
                                        min="0"
                                        value="<?php echo displayValue($current); ?>"
                                        required
                                    >
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="rate">Current Rate (sen/kWh)</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        id="rate"
                                        name="rate"
                                        step="any"
                                        min="0"
                                        value="<?php echo displayValue($rate); ?>"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="d-flex flex-wrap">
                                <button type="submit" class="btn btn-primary mr-2 mb-2">Calculate</button>
                                <a href="index.php" class="btn btn-outline-secondary mb-2">Reset</a>
                            </div>
                        </form>

                        <?php if ($results): ?>
                            <hr class="my-4">

                            <div class="summary-box">
                                <p>Power : <?php echo formatTableNumber($results['powerKilowatts']); ?>kw</p>
                                <p>Rate : <?php echo formatTableNumber($results['rateInRm'], 3); ?>RM</p>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Hour</th>
                                            <th>Energy (kWh)</th>
                                            <th>Total (RM)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($results['tableRows'] as $index => $row): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo $row['hour']; ?></td>
                                                <td><?php echo formatTableNumber($row['energy']); ?></td>
                                                <td><?php echo formatTableNumber($row['total'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
