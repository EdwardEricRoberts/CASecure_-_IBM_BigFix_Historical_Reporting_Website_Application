<!-- <!DOCTYPE html> -->
<html>
<head>
<title>Table Experiment 2</title>

<link rel="stylesheet" type="text/css" href="TABLE_FORMAT_1.css">



<link rel="stylesheet" href="table-sortable.css">
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous">
</script>
<script src="table-sortable.js"></script>



<?php require 'includes/header.php'; ?>

<?php require 'includes/alert.php'; ?>

<?php require 'includes/navigation.php'; ?>


<br>
<div class="span-5 last">

	<span class="breadcrumbs">You are here: Dashboards</span><br>
</div>
	<br>
	<div class="prepend-5 span-5">
	 <!--<div class="divtester">hello</div> -->
		<div class="pagetitle2">Dashboards</div>
	</div>
<br><br>
<script>

</script>

<br>


<!-- insert sample table here -->

<div id="myTable"></div>
<input type="search" id="search">
<script>
var data = [
    {
        formCode: 531718,
        formName: 'Investment Form',
        fullName: 'Test User',
        appointmentDate: '13 March, 2017',
        appointmentTime: '1:30PM',
        phone: '9876543210'
    },
    {
        formCode: 531790,
        formName: 'Investment Form 2',
        fullName: 'Test User',
        appointmentDate: '12 March, 2017',
        appointmentTime: '1:30PM',
        phone: '9876543210'
    },
    {
        formCode: 531334,
        formName: 'Investment Form 3',
        fullName: 'Test User',
        appointmentDate: '10 March, 2017',
        appointmentTime: '1:30PM',
        phone: '9876543210'
    },
    {
        formCode: 5317,
        formName: 'Investment Form 4',
        fullName: 'Test User',
        appointmentDate: '17 March, 2017',
        appointmentTime: '1:30PM',
        phone: '9876543210'
    },
    {
        formCode: 5318,
        formName: 'Investment Form 4',
        fullName: 'Test User',

        appointmentDate: '17 March, 2017',

        appointmentTime: '1:30PM',
        phone: '9876543210'
    },
    {
        formCode: 5319,
        formName: 'Investment Form 4',
        fullName: 'Test User',
        appointmentDate: '17 March, 2017',
        appointmentTime: '1:30PM',
        phone: '9876543210'
    },
    {
        formCode: 5320,
        formName: 'Investment Form 4',
        fullName: 'Test User',
        appointmentDate: '17 March, 2017',
        appointmentTime: '1:30PM',
        phone: '9876543210'
    },
    {
        formCode: 5321,
        formName: 'Investment Form 4',
        fullName: 'Test User',
        appointmentDate: '17 March, 2017',
        appointmentTime: '1:30PM',
        phone: '9876543210'
    },
    {
        formCode: 5322,
        formName: 'Investment Form 4',
        fullName: 'Test User',
        appointmentDate: '17 March, 2017',
        appointmentTime: '1:30PM',
        phone: '9876543210'
    },
    {
        formCode: 5323,
        formName: 'Investment Form 4',
        fullName: 'Test User',
        appointmentDate: '17 March, 2017',
        appointmentTime: '1:30PM',
        phone: '9876543210'
    },
    {
        formCode: 5324,
        formName: 'Investment Form 4',
        fullName: 'Test User',
        appointmentDate: '17 March, 2017',
        appointmentTime: '1:30PM',
        phone: '9876543210'
    },
    {
        formCode: 5325,
        formName: 'Investment Form 4',
        fullName: 'Test User',
        appointmentDate: '17 March, 2017',
        appointmentTime: '1:30PM',
        phone: '9876543210'
    },
    {
        formCode: 5326,
        formName: 'Investment Form 4',
        fullName: 'Test User',
        appointmentDate: '17 March, 2017',
        appointmentTime: '1:30PM',
        phone: '9876543210'
    },
    {
        formCode: 5327,
        formName: 'Investment Form 4',
        fullName: 'Test User',
        appointmentDate: '17 March, 2017',
        appointmentTime: '1:30PM',
        phone: '9876543210'
    },
    {
        formCode: 5328,
        formName: 'Investment Form 4',
        fullName: 'Test User',
        appointmentDate: '17 March, 2017',
        appointmentTime: '1:30PM',
        phone: '9876543210'
    },
    {
        formCode: 5329,
        formName: 'Investment Form 4',
        fullName: 'Test User',
        appointmentDate: '17 March, 2017',
        appointmentTime: '1:30PM',
        phone: '9876543210'
    },
]
 
var columns = {
    'formCode': 'Form Code',
    'formName': 'Form Name',
    'fullName': 'Full Name',
    'appointmentDate': 'Appointment Date',
    'appointmentTime': 'Appointment Time',
    'phone': 'Phone'
}

var TestData = {
    data: data,
    columns: columns
}
 
var table = $('#myTable').tableSortable({
    data: TestData.data,
    columns: TestData.columns,
    <a href="https://www.jqueryscript.net/time-clock/">date</a>Parsing: true,
    columnsHtml: function(value, key) {
      return value;
    },
    pagination: 5,
    showPaginationLabel: true,
    prevText: 'Prev',
    nextText: 'Next',
    searchField: $('#search'),
    responsive: [
      {
        maxWidth: 992,
        minWidth: 769,
        columns: TestData.col,
        pagination: true,
        paginationLength: 3
      },
      {
        maxWidth: 768,
        minWidth: 0,
        columns: TestData.colXS,
        pagination: true,
        paginationLength: 2
      }
    ]
})

</script>

<div class="pages"></div>


<!-- end sample table -->




<?php require 'includes/footer.php'; ?>