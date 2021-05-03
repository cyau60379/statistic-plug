<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
<script>
    window.onload = function () {

        var chart = new CanvasJS.Chart("chartContainer", {
            title: {
                text: "Vistors"
            },
            axisY: {
                title: "Number of connections"
            },
            data: [{
                type: "line",
                dataPoints: <?php echo json_encode($plug->sp_chart_data(), JSON_NUMERIC_CHECK); ?>
            }]
        });
        chart.render();

    }
</script>
<div class="wrap">
    <h1>Number of visitors:
        <?php
        $plug->sp_count_visits();
        ?>
    </h1>
    <div id="chartContainer" style="height: 370px; width: 100%;"></div>
</div>