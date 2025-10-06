    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    
    mobileMenuBtn.addEventListener('click', function() {
        this.classList.toggle('open');
        sidebar.classList.toggle('open');
        overlay.classList.toggle('open');
    });
    
    overlay.addEventListener('click', function() {
        mobileMenuBtn.classList.remove('open');
        sidebar.classList.remove('open');
        this.classList.remove('open');
    });
    
    const sidebarLinks = document.querySelectorAll('.sidebar .nav-item');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            mobileMenuBtn.classList.remove('open');
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        });
    });