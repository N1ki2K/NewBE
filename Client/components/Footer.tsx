import React from 'react';

const Footer: React.FC = () => (
  <footer className="bg-brand-blue text-white">
    <div className="container mx-auto px-4 sm:px-6 lg:px-8 py-6 text-center text-sm sm:text-base space-x-1">
      <span>© 2025</span>
      <a
        href="https://tsa-soft.com"
        target="_blank"
        rel="noopener noreferrer"
        className="text-brand-gold-light hover:text-white transition-colors"
      >
        Tsa-Soft
      </a>
      <span>. Всички права запазени.</span>
    </div>
  </footer>
);

export default Footer;
