import React, { useEffect } from 'react';
import AOS from 'aos';
import 'aos/dist/aos.css';
import './Gallery.css';

const Gallery = () => {
  useEffect(() => {
    AOS.init({
      duration: 800,
      easing: 'ease-in-out',
      once: true,
    });
  }, []);

  const images = [
    'assets/img/photo_2025-06-03_22-58-14.jpg',
    'assets/img/photo_2025-07-08_20-20-05.jpg',
    'assets/img/photo_2025-07-22_22-35-40.jpg',
    'assets/img/photo_2025-08-01_00-46-17.jpg',
    'assets/img/photo_2025-11-09_02-14-41.jpg',
    'assets/img/photo_2025-11-21_17-50-59.jpg',
    'assets/img/photo_2025-11-22_00-52-02.jpg',
    'assets/img/photo1.jpg',
    'assets/img/photo2.jpg',
    'assets/img/photo3.jpg',
    'assets/img/photo4.jpg',
    'assets/img/photo6.jpg',
  ];

  return (
    <div className="gallery-wrapper">
      <div className="gallery">
        {images.map((src, index) => (
          <div
            key={index}
            className="gallery-item"
            data-aos="fade-up"
            data-aos-delay={index * 50}
          >
            <img src={src} alt={`Gallery ${index + 1}`} />
          </div>
        ))}
      </div>
    </div>
  );
};

export default Gallery;
