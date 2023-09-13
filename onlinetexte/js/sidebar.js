
document.addEventListener("DOMContentLoaded", function () 
{
    const alertLink = document.getElementById("sidebarbutton");
    const sidebar = document.getElementById("sidebar");
  
    alertLink.addEventListener("click", function (e) 
    {
      e.preventDefault(); 
      sidebar.classList.toggle("active"); 
    });
});
