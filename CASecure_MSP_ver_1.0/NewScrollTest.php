<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Bootstrap 3 scrollable dropdown</title>

  <!-- See Scrollable Menu with Bootstrap 3 http://stackoverflow.com/questions/19227496 -->

  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.js"></script>

  <script src="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.js"></script>
  <link rel="stylesheet" href="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.css">

  <style>
    .dropdown-menu {
      width: 100%;
    }

    .scrollable-menu {
      height: auto;
      max-height: 200px;
      overflow-x: hidden;
    }
  </style>
</head>

<body>

        
        <div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            Scrollable Menu <span class="caret"></span>
          </button>
          <ul class="dropdown-menu scrollable-menu" role="menu">
            <li><a href="#">Action 1</a></li>
            <li><a href="#">Action 2</a></li>
            <li><a href="#">Action 3</a></li>
            <li><a href="#">Action 4</a></li>
            <li><a href="#">Action 5</a></li>
            <li><a href="#">Action 6</a></li>
            <li><a href="#">Action 7</a></li>
            <li><a href="#">Action 8</a></li>
            <li><a href="#">Action 9</a></li>
            <li><a href="#">Action 10</a></li>
            <li><a href="#">Action 11</a></li>
            <li><a href="#">Action 12</a></li>
            <li><a href="#">Action 13</a></li>
            <li><a href="#">Action 14</a></li>
            <li><a href="#">Very very very very very very long action</a></li>
          </ul>
        </div>
        <div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            Non Scrollable Menu <span class="caret"></span>
          </button>
          <ul class="dropdown-menu" role="menu">
            <li><a href="#">Action 1</a></li>
            <li><a href="#">Action 2</a></li>
            <li><a href="#">Action 3</a></li>
            <li><a href="#">Action 4</a></li>
            <li><a href="#">Action 5</a></li>
            <li><a href="#">Action 6</a></li>
            <li><a href="#">Action 7</a></li>
            <li><a href="#">Action 8</a></li>
            <li><a href="#">Action 9</a></li>
            <li><a href="#">Action 10</a></li>
            <li><a href="#">Action 11</a></li>
            <li><a href="#">Action 12</a></li>
            <li><a href="#">Action 13</a></li>
            <li><a href="#">Action 14</a></li>
            <li><a href="#">Very very very very very very long action</a></li>
          </ul>
        </div>
      
</body>

</html>
