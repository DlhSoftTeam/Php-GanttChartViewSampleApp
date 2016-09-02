<!DOCTYPE html>
<html>
<head>
    <title>GanttChartView Sample</title>
    <script type="text/javascript" src="DlhSoft.ProjectData.GanttChart.HTML.Controls.js"></script>
</head>
<body>
    <h1 style="font-family: Calibri, Arial">GanttChartView sample</h1>

    <form id="form" runat="server" method="POST">
        <div id="ganttChartView" style="height: 480px; margin-bottom: 4px;">...</div>
        <input id="changes" type="hidden" name="changes" />  
        <input type="submit" value="Save changes" />
    </form>

    <script type="text/javascript">
        // Retrieve and store the control and related elements for reference purposes.
        var ganttChartView = document.querySelector('#ganttChartView');
        var changes = document.querySelector('#changes');

        // Prepare  data items.
        var items = [
            
            <?php
            // Task class represents an item from the database.
            class Task {
                function Task($id, $name, $start, $finish) {
                    $this->id = $id;
                    $this->name = $name;
                    $this->start = $start;
                    $this->finish = $finish;
                }
            }

            // TODO: Replace the following lines with code that reads $tasks from the database.
            $tasks = array();
            for ($i = 0; $i < 32; $i++) {
                $start = mktime(0, 0, 0, date("m"), 2 + $i, date("Y")) * 1000; // milliseconds
                $finish = mktime(0, 0, 0, date("m"), 2 + $i + 3 + $i / 2, date("Y")) * 1000; // milliseconds
                array_push($tasks, new Task($i + 1, "Task " . ($i + 1), $start, $finish));
            }

            // Handle recorded client side changes.
            if ($_POST["changes"]){
                handle_changes($_POST["changes"], $tasks);
            }

            // Initialize (or reinitialize) the items collection on the client side based on server side task values (original or updated).
            foreach ($tasks as $task)
                echo "{ id: " . $task->id . ", content: '" . $task->name . "', start: new Date(" . $task->start . "), finish: new Date(" . $task->finish . "), isRelativeToTimezone: false},";
            ?>

        ];

        // Prepare control settings.
        var settings = {

            <?php
            echo "currentTime: new Date(" . date("Y") . ", " . (date("m") - 1) . ", 2, 12, 0, 0),";
            ?>

            gridWidth: '26%', chartWidth: '74%',
            isTaskCompletionReadOnly: true, // Completion not used in this sample
            areTaskPredecessorsReadOnly: true // Dependencies not used in this sample
        };

        // Remove not used grid columns.
        var columns = DlhSoft.Controls.GanttChartView.getDefaultColumns(items, settings);
        var indexOffset = columns[0].isSelection ? 1 : 0;
        columns.splice(indexOffset + 3, 3);
        settings.columns = columns;

        // Initialize the component.
        DlhSoft.Controls.GanttChartView.initialize(ganttChartView, items, settings);

        // Record client side item changes to be sent back to the server side upon postback, to be handled there.
        settings.itemPropertyChangeHandler = function (item, propertyName, isDirect, isFinal){
            if ((propertyName == 'content' || propertyName == 'start' || propertyName == 'finish') && isFinal ) {
                item.hasChanges = true;
                changes.value = getChangesAsString();
            }
        };

        function getChangesAsString() {
            var changesString = '';
            for (var i = 0; i < items.length; i++) {
                var item = items[i];
                if (!item.hasChanges)
                    continue;
                if (changesString.length > 0)
                    changesString += '\n';
                changesString += item.id + '\t' + item.content + '\t' + item.start.valueOf() + '\t' + item.finish.valueOf();
            }
            return changesString;
        }

        <?php
        // Define the way changes are handled upon postback, i.e. updating Task objects accordingly and sending their data values back to the database.
        function handle_changes($changesString, &$tasks) {
            $changes = explode("\n", $changesString);
            foreach ($changes as $change) {
                $changeFields = explode("\t", $change);
                $id = $changeFields[0];
                foreach ($tasks as $task) {
                    if ($task->id == $id) {
                        $task->content = $changeFields[1];
                        $task->start = $changeFields[2];
                        $task->finish = $changeFields[3];
                        // TODO: Also update $task item in the database.
                        break;
                    }
                }
            }
        }
        ?>

    </script>
</body>
</html>