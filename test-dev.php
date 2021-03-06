<!--

This test is meant for using while developing. It forces reloading of cache etc.
-->
<!DOCTYPE HTML>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>HTML upload test</title>
		<meta http-equiv="Cache-Control" content="no-cache, must-revalidate">
		<meta http-equiv="Expires" content="Sat, 26 Jul 1997 05:00:00 GMT">
		<script type="text/javascript" src="jquery-1.3.2.min.js" charset="utf-8"></script>
		<script type="text/javascript" src="hunch-upload.js?r=<?=time()?>" charset="utf-8"></script>
		<script type="text/javascript" charset="utf-8">
			hunch.debug = true; // enables debug logging to console
			hunch.devel = true; // enables experimental stuff

			// we trigger this when the user has choosen a file (onchange)
			function upload(fileInputElement) {
				// save references to out progress bar's components
				var progressIndicator = document.getElementById('determinable-progress-indicator');
				var progressBg = document.getElementById('determinable-progress-bg');
				var progressBar = document.getElementById('determinable-progress-bar');
				var progressLabel = document.getElementById('determinable-progress-label');

				var files = []; // set of files which are being uploaded in this batch
				var completed = 0; // number of files reported as completed

				// we call this whenever a file's progress changes to recompute and redraw
				// our progress bar.
				var updateTotalProgress = function() {
					var bytesLoaded = 0;
					var bytesTotal = 0;
					// summarize total and loaded bytes
					for (var i in files) {
						var f = files[i];
						if (!f) continue;
						bytesLoaded += f.bytesLoaded;
						bytesTotal += f.bytesTotal;
					}
					// derive a value between 0-1 which represents relative progress
					var progress = bytesTotal > 0 && bytesLoaded > 0 ? bytesLoaded / bytesTotal : 0.0;
					// set the width of the progress bar
					progressBar.style.width = (progress * progressBg.offsetWidth)+'px';
					// update the text label next to the progress bar
					progressLabel.innerHTML = (Number(progress*100).toFixed(0)) + '%, '+
						Number(bytesLoaded/1024).toFixed(1)+' of '+
						Number(bytesTotal/1024).toFixed(1) + ' kB, '+
						files.length + ' file(s)';
				};

				// switch out the file box for a progress indicator
				progressIndicator.style.display = "block";
				fileInputElement.style.display = "none";

				// call hunch.upload() with a custom handler
				files = hunch.upload(fileInputElement, function(file) {
					console.log('uploading', file.name, file);
					// when an unpload completes...
					file.onComplete = function(textStatus, responseData) {
						// ...log some info to console
						console.log('COMPLETE (status: '+textStatus+')', file.file.fileName);
						console.log('response:', responseData);
						// ...increase number of completed uploads
						completed++;
						// if all files have been uploaded, restore state so the user can
						// upload more files, starting over again.
						if (completed >= files.length) {
							// timeout to let the browser redisplay
							setTimeout(function(){
								progressIndicator.style.display = "none";
								fileInputElement.value = '';
								fileInputElement.style.display = "block";
							},100);
						}
					};
					// when an upload was aborted...
					file.onAbort = function() {
						// ..set progress to 100% (otherwise our unified progress meter will never reach 100%)
						this.bytesTotal = this.bytesLoaded;
						this.progress = 1.0;
						updateTotalProgress();
					};
					// when an upload reports that progress has been made, call our updateTotalProgress()
					file.onProgress = updateTotalProgress;
					// start the upload of this file
					file.send('receive.php');
				});
			}
		</script>
		<style type="text/css" media="screen">
			#determinable-progress-indicator { display:none; }
			#determinable-progress-indicator .bg { width:200px; height:10px; background:#ccc; float:left; }
			#determinable-progress-indicator .bar { width:0px; height:10px; background:#222; }
			#determinable-progress-indicator .label {
				float:left; margin:0 0 0 5px; font-family:sans-serif; font-size:11px; }
		</style>
	</head>
	<body>
		<header>
			<h1>HTML upload test</h1>
		</header>
		<section>
			<input type="file" name="file" id="files" onchange="upload(this)" multiple>
			<div id="determinable-progress-indicator">
				<div class="bg" id="determinable-progress-bg"><div class="bar" id="determinable-progress-bar"></div></div><p class="label" id="determinable-progress-label"></p>
			</div>
		</section>
	</body>
</html>
