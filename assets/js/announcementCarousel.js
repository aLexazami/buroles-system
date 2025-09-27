export function initAnnouncementCarousel() {
  const slides = document.querySelectorAll('.announcement-slide');
  const dots = document.querySelectorAll('.dot');
  const dotTrack = document.getElementById('dot-track');
  const maxVisibleDots = 10;
  const dotSize = 24;
  let current = 0;

  function showSlide(index) {
    slides.forEach((slide, i) => {
      slide.classList.toggle('hidden', i !== index);
    });

    dots.forEach((dot, i) => {
      dot.classList.toggle('opacity-100', i === index);
      dot.classList.toggle('bg-emerald-500', i === index);
      dot.classList.toggle('bg-gray-300', i !== index);
    });

    const offset = Math.max(0, index - Math.floor(maxVisibleDots / 2));
    dotTrack.style.transform = `translateX(-${offset * dotSize}px)`;
  }

  document.getElementById('prev-announcement')?.addEventListener('click', () => {
    current = (current - 1 + slides.length) % slides.length;
    showSlide(current);
  });

  document.getElementById('next-announcement')?.addEventListener('click', () => {
    current = (current + 1) % slides.length;
    showSlide(current);
  });

  dots.forEach(dot => {
    dot.addEventListener('click', () => {
      current = parseInt(dot.dataset.index);
      showSlide(current);
    });
  });

  showSlide(current);
}