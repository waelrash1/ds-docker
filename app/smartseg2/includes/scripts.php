<script type="text/javascript">
  function showHide(div1, div2) {
    if (document.getElementById(div1).style.visibility == 'visible') {
      document.getElementById(div1).style.visibility = 'hidden';
      document.getElementById(div2).style.visibility = 'visible';
    }
    else{
      document.getElementById(div1).style.visibility = 'visible';
      document.getElementById(div2).style.visibility = 'hidden';
    }

    return false;
  }
</script>
