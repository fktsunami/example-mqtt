<html>
<head>
    <title>K-TEC Faker</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="k-tec.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
</head>
<body>
<script type="text/javascript">
    function Log(msg,divName){var msgHtml="<p><u>Time "+Date.now()+"</u>: "+msg+"</p>";divName.insertAdjacentHTML("afterbegin",msgHtml)}
</script>
<h1>K-TEC EXAMPLE CONTROLLER</h1>
<div class="block" id="connecting-div"></div>
<div id="explorer-div" style="visibility:hidden">
    Mission ID: <input title="Mission ID" type="text" id="missionId" value=""><br><br>
    Manufacture: <input title="Manufacture" type="text" id="manufacture" value="PRODRONE"><br><br>
    Drone Serial No.: <input title="Drone ID" type="text" id="serial" value="PD1000579"><br><br>
    Flight Schedule: <input title="Flight Schedule" type="text" id="flightSchedule" value="2018-01-22 12:00:00"><br><br>
    Step 1:
    <button onclick="subscribeDroneTopicKTEC()">Subscribe Drone Topic</button>
    <button onclick="">Disconnect with application</button>
    <br><br>
    Step 2:
    <!-- <button onclick="publishMission()">Publish Mission</button> -->
    <button onclick="schedule()">Set Flight Schedule</button>
    <br><br>
    Step 3:
    <button onclick="startMission()" id="startMission" disabled>Start Mission</button>
    <br>
    <!-- <div class="settings" id="settings-header-div">
       <p>publish to topic:<input type="text" name="publish-topic" id="publish-topic" value="publish-topic" onchange="updatePublishTopic()"></p>
    </div>
    <div class="settings" id="settings-div">
       <p>string:<input type="text" name="publish-data" id="publish-data" size="45" onchange="updatePublishData()"></p>
    </div>
    <div class="subscribe" id="subscribe-header-div">
       <p>subscribe to topic:<input type="text" id="subscribe-topic" name="subscribe-topic" value="subscribe-topic" onchange="updateSubscriptionTopic()"/><input type="button" id="clear-button" name="clear" value="clear history" onclick="clearHistory()"/></p>
    </div> -->
    <br><br>
    <div class="subscribe" id="subscribediv">
    </div>
    <input type="hidden" id="token" value="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczovL2FwaS1kZXYudGVycmEtdXRtLmNvbS92MS9sb2dpbiIsImlhdCI6MTUxNjU4NTgxNCwiZXhwIjoxNTE3MTkwNjE0LCJuYmYiOjE1MTY1ODU4MTQsImp0aSI6IkNDN3hTQWZ1SkRudndFUlAiLCJzdWIiOjEsInBydiI6IjUxN2IzZWVlYjRkNzcwMDE1NDczYmE5M2I2NzljNWY0NDg4MmE5NTUifQ.bjRdtFW47Kr-fnRxAiC8XpuVuZtupqC8H4ixpWqXJqA">
</div>
<script type="text/javascript">
  function joinroom(){
    missionId = document.getElementById("missionId").value;
    token = document.getElementById("token").value;
    homePosition.lat = parseFloat(document.getElementById("homePositionLat").value);
    homePosition.lng = parseFloat(document.getElementById("homePositionLng").value);
    PDID = document.getElementById("serial").value;

    request_room = {
      'flight_id'     : missionId,
      'type'          : 'app',
      'token'         : token
    };

    let date = new Date()
    let y = date.getFullYear()
    let m = date.getMonth() + 1
    let d = date.getDate()
    let h = date.getHours()
    let i = date.getMinutes()
    let s = date.getSeconds()
    let str = '' + y + (m<=9 ? '0' + m : m) + (d <= 9 ? '0' + d : d) + (h <= 9 ? '0' + h : h) + (i <= 9 ? '0' + i : i) + (s <= 9 ? '0' + s : s)

    current_position = {
      'flight_id'		: missionId,
      'lat'			: homePosition.lat,
      'lng'			: homePosition.lng,
      'direction'		: 0,
      'height'		: 20,
      'flight_time'	: str.slice(2,100),
      'token'			: token
    }

    socket.emit('REQUEST_ROOM', request_room);

    socket.on('REQUEST_ROOM_SUCCEED_APP', function(flightIDResponse){
      console.log(flightIDResponse);
      if(missionId == flightIDResponse){
        socket.on('GET_FLIGHT_DETAIL_INFO_APP', function(flightData){
          Log('Request Room Succeed for Flight: '+missionId, subscribediv);
          Log('Flight Data: '+JSON.stringify(flightData), subscribediv);

          //TODO Generate .XML File
          var _url = xmlGenerator;
          $.ajax({
            url: _url,
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(flightData),
            success: xmlGenerateSucceed
          });
        });
      }
    });
  }

  function joinroomByMQTT () {
    missionId = document.getElementById("missionId").value;
    token = document.getElementById("token").value;
    homePosition.lat = parseFloat(document.getElementById("homePositionLat").value);
    homePosition.lng = parseFloat(document.getElementById("homePositionLng").value);
    PDID = document.getElementById("serial").value;

    request_room = {
      'flight_id'     : missionId,
      'type'          : 'app',
      'token'         : token
    };

    let date = new Date()
    let y = date.getFullYear()
    let m = date.getMonth() + 1
    let d = date.getDate()
    let h = date.getHours()
    let i = date.getMinutes()
    let s = date.getSeconds()
    let str = '' + y + (m<=9 ? '0' + m : m) + (d <= 9 ? '0' + d : d) + (h <= 9 ? '0' + h : h) + (i <= 9 ? '0' + i : i) + (s <= 9 ? '0' + s : s)

    current_position = {
      'flight_id'     : missionId,
      'lat'           : homePosition.lat,
      'lng'           : homePosition.lng,
      'direction'     : 0,
      'height'        : 20,
      'flight_time'   : str.slice(2,100),
      'token'         : token
    }

    socket.emit('REQUEST_ROOM', request_room);

    socket.on('REQUEST_ROOM_SUCCEED_APP', function(flightIDResponse){
      console.log(flightIDResponse);
      if(missionId == flightIDResponse){
        socket.on('GET_FLIGHT_DETAIL_INFO_APP', function(flightData) {
          Log('Request Room Succeed for Flight: ' + missionId, subscribediv);
          Log('Flight Data: ' + JSON.stringify(flightData), subscribediv);

          //TODO Generate .XML File
          var _url = xmlGenerator;
          $.ajax({
            url: _url,
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(flightData),
            success: xmlGenerateSucceed
          });
        });
      }
    });
  }

  function xmlGenerateSucceed(data){
    var resultGenerateFile = JSON.parse(data);
    if(resultGenerateFile.status){
      KML_File = '/tmp/'+resultGenerateFile.data.name;
      Log('KML File Generated: /tmp/'+resultGenerateFile.data.name+' - '+resultGenerateFile.data.byte+' bytes', subscribediv);

      uploadKMLFile();
    }
    else
      Log('KML File Generated: '+resultGenerateFile.status, subscribediv);
  }
  function prepare(){
    socket.emit('APP_PREPARE_FLIGHT_SUCCEED', current_position);

    socket.on('EVENT_APP_PREPARE_FLIGHT_FAILED', function(){
      Log('Create Datelist failed for Flight: '+flightId, subscribediv);
    });

    socket.on('EVENT_APP_PREPARE_FLIGHT_SUCCEED', function(datelist){
      datelistId = datelist.id;
      Log('Prepare Flight Succeed for Flight: '+flightId+' Datelist: '+datelistId, subscribediv);

      socket.on('SYNC', function(dataDrones){
        console.log(dataDrones);
      });
      socket.on('START_FLIGHT_TO_APP',function(){
        console.log('START_FLIGHT_TO_APP');
      });
      socket.on('CALL_GO_HOME_TO_APP',function(){
        console.log('CALL_GO_HOME_TO_APP');
      });
      socket.on('ADD_DRONE', function(drone){
        console.log('Add Drone');
        console.log(drone);
      });
    });
  }
  function sethome() {
    var drone = {
      'flight_id': 		flightId,
      'lat': 				current_position.lat,
      'lng': 				current_position.lng,
      'height': 			0,
      'token': 			token
    }
    console.log(drone);
    socket.emit('APP_SET_HOME_POSITION', drone);

    socket.on('EVENT_APP_SET_HOME_POSITION', function(homePosition){
      Log('Set Home Position Succeed for Flight: '+homePosition.flight_id+' Home Position: Latitude: '+homePosition.lat+' Longitude: '+homePosition.lng+' Altitude: '+homePosition.height, subscribediv);
    });
  }
  function disconnect(){
    var drone = {
      'datelist_id': 		datelistId
    }
    socket.emit('APP_DISCONNECTED_WITH_DRONE', drone);
  }
  function start(){
    var drone = {
      'datelist_id': 		datelistId
    }
    socket.emit('APP_DRONE_FLIGHT_STARTED', drone);
    Log('Drone Start Fly: '+PDID+' FlightID: '+datelistId, subscribediv);
    //TODO Call CMD to PRODRONE
    if(PDID) {
      var _url = controlPRODRONE + '?id=' + PDID + '&cmd=poweron';
      $.ajax({
        url: _url,
        type: "POST",
        contentType: "application/json",
        success: cmdSucceed
      });
    }
  }
  function poweron(argument) {
    //TODO Call CMD to PRODRONE
    if(PDID) {
      var _url = controlPRODRONE + '?id=' + PDID + '&cmd=poweron';
      $.ajax({
        url: _url,
        type: "POST",
        contentType: "application/json",
        success: cmdSucceed
      });
    }
  }
  function autostart(){
    //TODO Call CMD to PRODRONE
    if(PDID) {
      var _url = controlPRODRONE + '?id=' + PDID + '&cmd=autostart';
      $.ajax({
        url: _url,
        type: "POST",
        contentType: "application/json",
        success: cmdSucceed
      });
    }
  }
  function pause() {
    var drone = {
      'datelist_id': 		datelistId
    }
    socket.emit('APP_DRONE_COLLISION_PAUSE', drone);
    isPaused = true;
    Log('Drone Paused: '+PDID+' Datelist: '+datelistId, subscribediv);
    //TODO Call CMD to PRODRONE
    if(PDID) {
      var _url = controlPRODRONE + '?id=' + PDID + '&cmd=autopause';
      $.ajax({
        url: _url,
        type: "POST",
        contentType: "application/json",
        success: cmdSucceed
      });
    }
  }
  function resume(){
    var drone = {
      'datelist_id': 		datelistId
    }
    socket.emit('APP_DRONE_COLLISION_RESUME', drone);
    isPaused = false;
    Log('Drone Resumed: '+PDID+' Datelist: '+datelistId, subscribediv);
    //TODO Call CMD to PRODRONE
    if(PDID) {
      var _url = controlPRODRONE + '?id=' + PDID + '&cmd=autoresume';
      $.ajax({
        url: _url,
        type: "POST",
        contentType: "application/json",
        success: cmdSucceed
      });
    }
  }
  function stop(){
    var drone = {
      'datelist_id': 		datelistId
    }
    socket.emit('APP_DRONE_COLLISION_STOP', drone);
    clearInterval(loopSendLog);
    Log('Drone stopped: '+PDID+' Datelist: '+datelistId, subscribediv);
    //TODO Call CMD to PRODRONE
    if(PDID) {
      var _url = controlPRODRONE + '?id=' + PDID + '&cmd=autostop';
      $.ajax({
        url: _url,
        type: "POST",
        contentType: "application/json",
        success: cmdSucceed
      });
    }
  }

  // start - Shiotsuka added 20th Dec.
  function light(device, operation, interval, duty) {
    var drone = {
      'datelist_id': 		datelistId
    }
    socket.emit('APP_DRONE_COLLISION_LIGHT', drone);
    clearInterval(loopSendLog);
    Log('Light: '+flightId+' Datelist: '+datelistId, subscribediv);
    //TODO Call CMD to PRODRONE
    if(PDID) {
      var _url = controlPRODRONE + '?id=' + PDID + '&cmd=light&device=' + device + '&operation=' + operation + '&interval=' + interval + '&duty=' + duty;
      $.ajax({
        url: _url,
        type: "POST",
        contentType: "application/json",
        success: cmdSucceed
      });
    }
  }
  function sound(device, operation, source) {
    Log('Sound: '+flightId+' Datelist: '+datelistId, subscribediv);
    //TODO Call CMD to PRODRONE
    if(PDID) {
      var _url = controlPRODRONE + '?id=' + PDID + '&cmd=sound&device=' + device + '&operation=' + operation + '&source=' + source;
      $.ajax({
        url: _url,
        type: "POST",
        contentType: "application/json",
        success: cmdSucceed
      });
    }
  }
  function camera(device, operation, parameter, pdid) {
    Log('Camera: '+flightId+' Datelist: '+datelistId, subscribediv);
    //TODO Call CMD to PRODRONE
    PDID = pdid === null ? PDID : pdid;
    if(PDID) {
      var _url = controlPRODRONE + '?id=' + PDID + '&cmd=camera&device=' + device + '&operation=' + operation + '&parameter=' + parameter;
      $.ajax({
        url: _url,
        type: "POST",
        contentType: "application/json",
        success: cmdSucceed
      });
    }
  }
  function goto(latitude, longitude, relheight, speed) {
    Log('Go To: '+flightId+' Datelist: '+datelistId, subscribediv);
    //TODO Call CMD to PRODRONE
    if(PDID) {
      var _url = controlPRODRONE + '?id=' + PDID + '&cmd=goto&latitude=' + latitude + '&longitude=' + longitude + '&relheight=' + relheight + '&speed=' + speed;
      $.ajax({
        url: _url,
        type: "POST",
        contentType: "application/json",
        success: cmdSucceed
      });
    }
  }
  // end - Shiotsuka added 20th Dec.

  function landed(){
    var drone = {
      'datelist_id': 		datelistId
    }
    socket.emit('APP_DRONE_FLIGHT_SUCCEED', drone);
    Log('Drone landed: '+flightId+' Datelist: '+datelistId, subscribediv);
  }
  function uploadKMLFile(){
    if(KML_File && PDID) {
      var _url = uploadKMLURL + '?&id=' + PDID + '&pathFile=' + KML_File;
      $.ajax({
        url: _url,
        type: "POST",
        contentType: "application/json",
        success: uploadKMLSucceed
      })
      // start - Shiotsuka added 20th Dec.
        .done(function (data) {
          if (data.status === true) {
            Log('Upload succeed: ' + document.getElementById('manufacture').value + '/' + document.getElementById('serial').value + '/drone' + ':' + JSON.stringify(data.data), subscribediv);
          }
        })
        .fail(function (xhr) {
          if (xhr.responseJSON.status === false) {
            Log('Upload failed: ' + document.getElementById('manufacture').value + '/' + document.getElementById('serial').value + '/drone' + ':' + xhr.responseJSON.error.message[0], subscribediv);
          }
        })
      // end - Shiotsuka added 20th Dec.
    }
  }
  function uploadKMLSucceed(data){
    var resultGenerateFile = JSON.parse(data);
    if(resultGenerateFile.status){
      Log('KML File Upload: Succeed '+KML_File, subscribediv);
      prepareMissionByMqtt();
    }
    else
      Log('KML File Upload: Failed ', subscribediv);
  }
  function cmdSucceed(data){
    var resultGenerateFile = JSON.parse(data);
    if(resultGenerateFile.status){
      Log('Call CMD Succeed ', subscribediv);
    }
    else
      Log('Call CMD Failed ', subscribediv);
  }
  function sendlog2(){
    let date = new Date()
    let y = date.getFullYear()
    let m = date.getMonth() + 1
    let d = date.getDate()
    let h = date.getHours()
    let i = date.getMinutes()
    let s = date.getSeconds()
    let str = '' + y + (m<=9 ? '0' + m : m) + (d <= 9 ? '0' + d : d) + (h <= 9 ? '0' + h : h) + (i <= 9 ? '0' + i : i) + (s <= 9 ? '0' + s : s)

    var log = {
      id:				PDID,
      datelist_id: 	datelistId,
      status: 		0,
      message: 		'OK',
      datetime: 		str.slice(2,100),
      locate: 		{
        head: 			0,
        height: 		10,
        latitude: 		current_position.lat,
        longitude:		current_position.lng,
        speed: 			200,
        current_point: 	34180,
      },
      state: 			{
        battery: 		55,
        flight_time: 	str.slice(2,100),
        plan_status:  	1,
        go_home:  		0,
      },
      comm: 			{
        phone_no: 		'09011112222',
        range_out: 		0,
        antenna: 		5
      },
      token: 			token,
    };
    if (datelistId !== undefined) {
      loopSendLog = setInterval(function(){
        if(!isPaused) {
          log.locate.latitude += 0.0001;
          log.state.flight_time = parseInt(log.state.flight_time) + 1
          log.state.flight_time = "" + log.state.flight_time
          log.locate.head += 10;
          Log('Sent Log: '+JSON.stringify(log), subscribediv);
          socket.emit('SYNC', log);
        }
      }, 1000);
    } else {
      Log('Sent Log: Failed', subscribediv);
    }
  }
</script>
<script src="aws-iot-sdk-browser-bundle.js"></script>
<script src="bundle.js"></script>
</body>
</html>
