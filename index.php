<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "imobiliare";


$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedQuery = $_POST["query"];

    switch ($selectedQuery) {
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
                $sql = "SELECT * 
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
    }

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        echo "<h2>Query Results:</h2>";
        echo "<div class='modern-table'>";
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
               
            }
            ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imobiliare</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>


    <div class="top-bar">
        <img src="title_house.png" alt="Logo" class="logo">
        Imobiliare
    </div>

    <div class="background-rectangle"></div>

    <div class="main-container">
        <div class="left-section">
            <h1 style="font-size: 28px; color: #3A0CA3;">Selectează interogarea:</h1>
            <img src="index_image.png" alt="Index Image">
        </div>

        <div class="right-section">
        <div class="query-list column"> 
    <form id="queryForm" action="result.php" method="post">
        <ul>
            <li>
                <input type="text" id="adresa" name="adresa" placeholder="Introdu adresa">
                <br>
            </li>
            <li><button type="button" onclick="selectQuery('query1')">Interogare 1</button></li>
            <li><button type="button" onclick="selectQuery('query2')">Interogare 2</button></li>
            <li><button type="button" onclick="selectQuery('query3')">Interogare 3</button></li>
            <li><button type="button" onclick="selectQuery('query4')">Interogare 4</button></li>
            <li><button type="button" onclick="selectQuery('query5')">Interogare 5</button></li>
            <li><button type="button" onclick="selectQuery('query6')">Interogare 6</button></li>
            <li><button type="button" onclick="selectQuery('query7')">Interogare 7</button></li>
            <li><button type="button" onclick="selectQuery('query8')">Interogare 8</button></li>
        </ul>
  

                    <input type="hidden" name="selectedQuery" id="selectedQuery" value="">
                </form>
            </div>
        </div>
    </div>

    <script>
        function selectQuery(query) {
            document.getElementById('selectedQuery').value = query;
            document.getElementById('queryForm').submit();
        }
    </script>

</body>
</html>
