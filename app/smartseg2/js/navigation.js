function showpane(id, numsegs) {
  if (id != 'segmentsummary') {
    document.getElementById('segmentsummary').style.display = "none";
    document.getElementById('segmentsummarylink').style.backgroundColor = "#ddd";
  }
  for (seg = 1; seg <= numsegs; seg++) {
    segname = "segment" + seg;
    if (id != segname) {
      document.getElementById(segname).style.display = "none";
      document.getElementById(segname + 'link').style.backgroundColor = "#ddd";
    }
  }
  if (id != 'summaryfeatures') {
    document.getElementById('summaryfeatures').style.display = "none";
    document.getElementById('summaryfeatureslink').style.backgroundColor = "#ddd";
  }
  if (id != 'population') {
    document.getElementById('population').style.display = "none";
    document.getElementById('populationlink').style.backgroundColor = "#ddd";
  }

  document.getElementById(id + 'link').style.backgroundColor = "#999";
  document.getElementById(id).style.display = "block";
}

function showHisto(feature, numseg) {
  document.getElementById("impulse" + numseg).style.display = "none";
  document.getElementById("histo_" + numseg + "_" + feature).style.display = "block";
}

function backToImpulse(feature, numseg) {
  document.getElementById("histo_" + numseg + "_" + feature).style.display = "none";
  document.getElementById("impulse" + numseg).style.display = "block";
}

function showFeature(feature) {
  var featimps = document.getElementsByClassName("featureimpulse");
  for(var i = 0; i < featimps.length; i++) {
    featimps[i].style.display = "none";
  }

  var featlinks = document.getElementsByClassName("featurelink");
  for(var i = 0; i < featlinks.length; i++) {
    featlinks[i].style.backgroundColor = "#ddd";
  }

  document.getElementById("featurelink_" + feature).style.backgroundColor = "#999";
  document.getElementById("featureimpulse_" + feature).style.display = "block";
}

window.onload = function() {
  document.getElementById('globalview').onclick = function(event) {
    var span, input, text;
    event = event || window.event;
    span = event.target || event.srcElement;

    if (span && span.tagName.toUpperCase() === "SPAN") {
      span.style.display = "none";
      text = span.innerHTML;

      input = document.createElement("input");
      input.type = "text";
      input.size = text.length * 4 / 3;
      input.value = text;
      span.parentNode.insertBefore(input, span);

      input.focus();
      input.onblur = function() {
        span.parentNode.removeChild(input);
        span.innerHTML = input.value;
        span.style.display = "";
      };
    }
  };
};
