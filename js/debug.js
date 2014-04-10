var DEBUG = {
  assert: function(condition, message) {
    if (!condition) {
      if (message) {
        alert("ASSERT FAILED: " + message);
      } else {
        alert("ASSERT FAILED.");
      }
    }
  },
  
  spew: function(value, message) {
    console.log("DEBUG: " + message + ": " + JSON.stringify(value));
  }
}
