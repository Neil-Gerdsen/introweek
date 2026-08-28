<?php

$apiUrl = "https://api.scryfall.com/sets";
$csvFile = "../output/sets.csv";
if (!is_dir("../output")) {
    mkdir("../output", 0777, true);
}

// Officiële settypes
$officialSetTypes = [
    "core",
    "expansion",
    "masters",
    "commander",
    "draft_innovation",
    "starter",
    "box",
    "promo"
];

// Data ophalen
$context = stream_context_create([
    "http" => [
        "header" => "User-Agent: ScryfallSetExporter/1.0\r\nAccept: application/json\r\n"
    ]
]);

$json = file_get_contents($apiUrl, false, $context);

if ($json === false) {
    die("Fout: API kon niet worden bereikt.");
}

// JSON omzetten naar PHP-array
$data = json_decode($json, true);

if (!$data) {
    die("Fout: JSON kon niet worden verwerkt.");
}

// Sets filteren
$sets = [];

foreach ($data["data"] as $set) {

    if (!in_array($set["set_type"], $officialSetTypes)) {
        continue;
    }

    $sets[] = [
        "Code" => $set["code"],
        "Name" => $set["name"],
        "API_url" => $set["scryfall_uri"],
        "Released" => $set["released_at"],
        "Icon_url" => $set["icon_svg_uri"]
    ];
}

// Sorteren op releasedatum
usort($sets, function ($a, $b) {
    return strcmp($a["Released"], $b["Released"]);
});

// CSV openen
$file = fopen($csvFile, "w");

// Kolomnamen schrijven
fputcsv($file, [
    "Code",
    "Name",
    "API_url",
    "Released",
    "Icon_url"
], ",", '"', "\\");

// Sets naar CSV schrijven
foreach ($sets as $set) {
    fputcsv($file, $set, ",", '"', "\\");
}

// CSV sluiten
fclose($file);

echo "CSV-bestand is succesvol aangemaakt.";
