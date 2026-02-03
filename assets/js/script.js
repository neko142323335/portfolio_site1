// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      target.scrollIntoView({
        behavior: 'smooth'
      });
    }
  });
});

// Modal functionality
const modal = document.getElementById('image-modal');
const modalImg = document.getElementById('modal-img');
const modalText = document.getElementById('modal-text');
const closeBtn = document.getElementsByClassName('close')[0];

// Open modal when gallery item is clicked
document.querySelectorAll('.gallery-item').forEach(item => {
  item.addEventListener('click', function() {
    modal.style.display = 'block';
    modalImg.src = this.dataset.src;
    modalText.innerHTML = this.dataset.caption;
    console.log('Modal opened for:', this.dataset.src);
  });
});

// Close modal when close button is clicked
closeBtn.onclick = function() {
  modal.style.display = 'none';
}

// Close modal when clicking outside the modal content
modal.onclick = function(event) {
  if (event.target === modal) {
    modal.style.display = 'none';
  }
}

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape' && modal.style.display === 'block') {
    modal.style.display = 'none';
  }
});

// Add some animation to gallery items on scroll
const observerOptions = {
  threshold: 0.1,
  rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.style.opacity = '1';
      entry.target.style.transform = 'translateY(0)';
    }
  });
}, observerOptions);

document.querySelectorAll('.gallery-item').forEach(item => {
  item.style.opacity = '0';
  item.style.transform = 'translateY(20px)';
  item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
  observer.observe(item);
});


