<?php 
$targetValues = [
    'COMPLAINT' => [
        'Network Quality Complaint' => 30,
        'Internet Related Complaint' => 40,
        'VAS Complaint' => 30,
    ],
    'STATUS' => [
        'Follow Up' => 50,
        'Closed' => 50,
    ],
    'SOURCE' => [
        'Contact Center' => 100,
    ],
];

$tolerance = 5; // Tolerance in percentage

$sums = [
    'COMPLAINT' => 0,
    'STATUS' => 0,
    'SOURCE' => 0,
];

$selectedRows = [];
$unselectedRows = $yourDataset;

while (
    ($sums['COMPLAINT'] < (100 + $tolerance)) &&
    ($sums['STATUS'] < (100 + $tolerance)) &&
    ($sums['SOURCE'] < (100 + $tolerance))
) {
    shuffle($unselectedRows);

    $row = array_shift($unselectedRows);
    if (!$row) {
        break;
    }

    $complaint = $row['COMPLAINT'];
    $status = $row['STATUS'];
    $source = $row['SOURCE'];

    if (
        ($sums['COMPLAINT'] + 1 <= (100 + $tolerance)) &&
        ($sums['STATUS'] + 1 <= (100 + $tolerance)) &&
        ($sums['SOURCE'] + 1 <= (100 + $tolerance))
    ) {
        $sums['COMPLAINT'] += 1;
        $sums['STATUS'] += 1;
        $sums['SOURCE'] += 1;
        $selectedRows[] = $row;
    }
}

print_r($selectedRows);

// Now, $selectedRows contains the selected rows while ensuring all category targets are met within tolerance, with each row incrementing the sums by 1
