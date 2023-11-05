document.addEventListener("DOMContentLoaded", function() 
{
    var text = document.getElementById("extraLinks");
    var linksArr = links.split(" ");
    text.innerHTML = "Additional Links: ";

    for(var i=0; i < linksArr.length; i++){
        text.innerHTML += "<br />";
        text.innerHTML += '<a href="'+linksArr[i]+'" target="_blank" rel="noopener noreferrer">'+linksArr[i]+'</a>';
    }
});
