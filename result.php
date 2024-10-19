<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "imobiliare";

// Create a connection
$conn = new mysqli($servername, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Execute the selected query
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["selectedQuery"])) {
    $selectedQuery = $_POST["selectedQuery"];

    switch ($selectedQuery) {
        // Handle each query case here
        case "query1":
            if (isset($_POST['adresa'])) {
                $adresaInput = $_POST['adresa'];
                $sql = "CALL GetSpatiuByAdresa('$adresaInput')";
            } else {
                echo "Adresa nu a fost introdusă.";
                exit;
            }
            break;
        case "query2":
            $sql = "SELECT * FROM Oferta WHERE vanzare = 'N' AND ((moneda = 'EUR' AND pret = 100) OR (moneda = 'RON' AND pret = 100 / 0.2) OR (moneda = 'USD' AND pret = 100 / 0.22))";
            break;
        
            case "query3":
                $sql = "SELECT Oferta.id_agentie, Oferta.id_spatiu, Oferta.vanzare, Oferta.pret, Oferta.moneda
                FROM Oferta
                JOIN Spatiu ON Oferta.id_spatiu = Spatiu.id_spatiu
                JOIN Tip ON Spatiu.id_tip = Tip.id_tip
                WHERE Tip.denumire = 'apartament' AND pret BETWEEN 25000 AND 40000;";
                break;
            
                case "query4":
                    $sql = "SELECT DISTINCT
                    s1.id_spatiu AS id_spatiu1,
                    s2.id_spatiu AS id_spatiu2
                FROM
                    Spatiu s1
                    JOIN Spatiu s2 ON s1.id_tip = s2.id_tip AND s1.id_spatiu < s2.id_spatiu
                    JOIN Oferta o1 ON s1.id_spatiu = o1.id_spatiu 
                    JOIN Oferta o2 ON s2.id_spatiu = o2.id_spatiu 
                    AND o1.id_agentie = o2.id_agentie;";
                    break;
                
                case "query5":
                    $sql = "SELECT *
                    FROM Spatiu
                    WHERE id_tip = (SELECT id_tip FROM Tip WHERE denumire = 'apartament')
                    AND suprafata >= ALL (SELECT suprafata FROM Spatiu WHERE id_tip = (SELECT id_tip FROM Tip WHERE denumire = 'apartament'));";
                    break;
        
                case "query6":
                    $sql = "SELECT DISTINCT A2.nume
                    FROM Agentie A2
                    WHERE EXISTS (
                        SELECT 1
                        FROM Oferta O1
                        JOIN Spatiu S1 ON O1.id_spatiu = S1.id_spatiu
                        JOIN Agentie A1 ON A1.id_agentie = O1.id_agentie
                        WHERE A1.id_agentie != A2.id_agentie
                          AND O1.id_spatiu = 1
                          AND EXISTS (
                              SELECT 1
                              FROM Oferta O2
                              JOIN Spatiu S2 ON O2.id_spatiu = S2.id_spatiu
                              WHERE O1.pret = O2.pret AND O1.moneda = O2.moneda AND S2.id_tip = S1.id_tip
                          )
                    );";
                    break;
                
                case "query7":
                    $sql = "SELECT moneda, AVG(pret) AS pret_mediu
                    FROM Oferta o
                    JOIN Spatiu s ON o.id_spatiu = s.id_spatiu
                    WHERE s.id_tip = (SELECT id_tip FROM Tip WHERE denumire = 'garaj')
                    AND o.vanzare = 'D'
                    GROUP BY moneda;";
                    break;
                    
                case "query8":
                    $sql = "SELECT zona, MIN(pret) AS pret_min, MAX(pret) AS pret_max
                    FROM Oferta o
                    JOIN Spatiu s ON o.id_spatiu = s.id_spatiu
                    JOIN Tip t ON s.id_tip = t.id_tip
                    WHERE t.denumire = 'apartament' AND o.vanzare = 'N'
                    GROUP BY zona;";
                    break;

        default:
            echo "Invalid query selection";
            exit; 
    }

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        echo "<h2 class='h2' style='margin-top: 60px;  font-family: 'Poppins', sans-serif;'> Rezultate interogari:</h2>"; 
        echo "<div class='modern-table' style='margin-top: 50px;'>";
        echo "<table>";
        
        echo "<thead>";
        echo "<tr>";
        while ($fieldInfo = $result->fetch_field()) {
            echo "<th>" . $fieldInfo->name . "</th>";
        }
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        $rowNum = 0;
        while ($row = $result->fetch_assoc()) {
            echo "<tr class='" . (($rowNum++ % 2 == 0) ? 'even' : 'odd') . "'>";
            foreach ($row as $value) {
                echo "<td>" . $value . "</td>";
            }
            echo "</tr>";
        }
        echo "</tbody>";
    
        echo "</table>";
        echo "</div>";
        $result->free();
    } else {
        echo "Error executing query: " . $conn->error;
    }

    $conn->close();
} else {
    echo "No query selected.";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Query Results</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="main-container">
    </div>

</body>
</html>