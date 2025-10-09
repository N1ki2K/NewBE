// Mock gallery data extracted from localhost.sql
// Based on josme_eventgallery_folder table

export interface GalleryAlbum {
  id: number;
  title: {
    bg: string;
    en: string;
  };
  date: string;
  published: boolean;
  hits: number;
  description?: string;
  images: GalleryImage[];
}

export interface GalleryImage {
  id: string;
  src: string;
  alt: {
    bg: string;
    en: string;
  };
}

export const mockGalleryAlbums: GalleryAlbum[] = [
  {
    id: 1,
    title: {
      bg: "Безопасност на движението",
      en: "Road Safety"
    },
    date: "2020-10-14",
    published: true,
    hits: 1739,
    description: "Снимки от дейности по безопасност на движението",
    images: [
      {
        id: "bdp-1",
        src: "/images/BDP/1.jpg",
        alt: { bg: "Безопасност на движението - дейност 1", en: "Road safety activity 1" }
      },
      {
        id: "bdp-2",
        src: "/images/BDP/2.jpg",
        alt: { bg: "Безопасност на движението - дейност 2", en: "Road safety activity 2" }
      },
      {
        id: "bdp-3",
        src: "/images/BDP/3.jpg",
        alt: { bg: "Безопасност на движението - дейност 3", en: "Road safety activity 3" }
      },
      {
        id: "bdp-4",
        src: "/images/BDP/4.jpg",
        alt: { bg: "Безопасност на движението - дейност 4", en: "Road safety activity 4" }
      },
      {
        id: "bdp-5",
        src: "/images/BDP/5.jpg",
        alt: { bg: "Безопасност на движението - дейност 5", en: "Road safety activity 5" }
      },
      {
        id: "bdp-6",
        src: "/images/BDP/6.jpg",
        alt: { bg: "Безопасност на движението - дейност 6", en: "Road safety activity 6" }
      },
      {
        id: "bdp-7",
        src: "/images/BDP/7.jpg",
        alt: { bg: "Безопасност на движението - дейност 7", en: "Road safety activity 7" }
      },
      {
        id: "bdp-8",
        src: "/images/BDP/8.jpg",
        alt: { bg: "Безопасност на движението - дейност 8", en: "Road safety activity 8" }
      }
    ]
  },
  {
    id: 2,
    title: {
      bg: "Образование за утрешния ден",
      en: "Education for Tomorrow"
    },
    date: "2020-05-29",
    published: true,
    hits: 1752,
    description: "Снимки от проекта Образование за утрешния ден",
    images: [
      {
        id: "edu-1",
        src: "/images/Obrazovanie_za_utreshniq_den/chasove_s_deca.jpg",
        alt: { bg: "Часове с деца", en: "Lessons with children" }
      },
      {
        id: "edu-2",
        src: "/images/Obrazovanie_za_utreshniq_den/chas_po_digitalni_ymeniq.jpg",
        alt: { bg: "Час по дигитални умения", en: "Digital skills lesson" }
      },
      {
        id: "edu-3",
        src: "/images/Obrazovanie_za_utreshniq_den/deca.jpg",
        alt: { bg: "Деца в училище", en: "Children at school" }
      },
      {
        id: "edu-4",
        src: "/images/Obrazovanie_za_utreshniq_den/digitalni.jpg",
        alt: { bg: "Дигитални технологии", en: "Digital technologies" }
      },
      {
        id: "edu-5",
        src: "/images/Obrazovanie_za_utreshniq_den/dig.ymeniq.jpg",
        alt: { bg: "Дигитални умения", en: "Digital skills" }
      },
      {
        id: "edu-6",
        src: "/images/Obrazovanie_za_utreshniq_den/display.jpg",
        alt: { bg: "Интерактивен дисплей", en: "Interactive display" }
      },
      {
        id: "edu-7",
        src: "/images/Obrazovanie_za_utreshniq_den/komp_kabinet.jpg",
        alt: { bg: "Компютърен кабинет", en: "Computer lab" }
      }
    ]
  },
  {
    id: 3,
    title: {
      bg: "Училищен живот",
      en: "School Life"
    },
    date: "2016-11-12",
    published: true,
    hits: 1781,
    description: "Снимки от ежедневието в училището",
    images: [
      {
        id: "school-1",
        src: "/images/begin.jpg",
        alt: { bg: "Начало на учебната година", en: "Beginning of school year" }
      },
      {
        id: "school-2",
        src: "/images/pic.jpg",
        alt: { bg: "Училищна сграда", en: "School building" }
      },
      {
        id: "school-3",
        src: "/images/oo.jpg",
        alt: { bg: "Училищни дейности", en: "School activities" }
      }
    ]
  },
  {
    id: 4,
    title: {
      bg: "Учители",
      en: "Teachers"
    },
    date: "2016-11-12",
    published: true,
    hits: 1769,
    images: [
      {
        id: "teachers-1",
        src: "https://picsum.photos/800/600?random=9",
        alt: { bg: "Учителски екип", en: "Teaching team" }
      },
      {
        id: "teachers-2",
        src: "https://picsum.photos/800/600?random=10",
        alt: { bg: "Учители в класната стая", en: "Teachers in classroom" }
      }
    ]
  },
  {
    id: 5,
    title: {
      bg: "Снимки от карате",
      en: "Karate Photos"
    },
    date: "2016-11-12",
    published: true,
    hits: 1929,
    images: [
      {
        id: "karate-1",
        src: "https://picsum.photos/800/600?random=11",
        alt: { bg: "Карате тренировка", en: "Karate training" }
      },
      {
        id: "karate-2",
        src: "https://picsum.photos/800/600?random=12",
        alt: { bg: "Ученици практикуват карате", en: "Students practicing karate" }
      },
      {
        id: "karate-3",
        src: "https://picsum.photos/800/600?random=13",
        alt: { bg: "Спортни постижения", en: "Sports achievements" }
      }
    ]
  },
  {
    id: 6,
    title: {
      bg: "Снимки от тържества",
      en: "Celebration Photos"
    },
    date: "2016-11-12",
    published: true,
    hits: 2000,
    images: [
      {
        id: "celebration-1",
        src: "https://picsum.photos/800/600?random=14",
        alt: { bg: "Училищно тържество", en: "School celebration" }
      },
      {
        id: "celebration-2",
        src: "https://picsum.photos/800/600?random=15",
        alt: { bg: "Ученици празнуват", en: "Students celebrating" }
      },
      {
        id: "celebration-3",
        src: "https://picsum.photos/800/600?random=16",
        alt: { bg: "Официална церемония", en: "Official ceremony" }
      }
    ]
  },
  {
    id: 7,
    title: {
      bg: "Снимки 09.02.2015",
      en: "Photos 09.02.2015"
    },
    date: "2016-11-12",
    published: true,
    hits: 1954,
    images: [
      {
        id: "feb-1",
        src: "https://picsum.photos/800/600?random=17",
        alt: { bg: "Февруарски събития", en: "February events" }
      },
      {
        id: "feb-2",
        src: "https://picsum.photos/800/600?random=18",
        alt: { bg: "Ученици участват в събития", en: "Students participating in events" }
      }
    ]
  },
  {
    id: 11,
    title: {
      bg: "24 май",
      en: "May 24th"
    },
    date: "2018-03-14",
    published: true,
    hits: 1930,
    description: "Ден на българската просвета и култура и на славянската писменост",
    images: [
      {
        id: "may24-1",
        src: "https://picsum.photos/800/600?random=19",
        alt: { bg: "Празник на буквите", en: "Day of the alphabet" }
      },
      {
        id: "may24-2",
        src: "https://picsum.photos/800/600?random=20",
        alt: { bg: "24 май празник", en: "May 24th celebration" }
      },
      {
        id: "may24-3",
        src: "https://picsum.photos/800/600?random=21",
        alt: { bg: "Ученици четат", en: "Students reading" }
      }
    ]
  },
  {
    id: 12,
    title: {
      bg: "8 март",
      en: "March 8th"
    },
    date: "2018-03-14",
    published: true,
    hits: 1925,
    description: "Международен ден на жената",
    images: [
      {
        id: "march8-1",
        src: "https://picsum.photos/800/600?random=22",
        alt: { bg: "Честване 8 март", en: "March 8th celebration" }
      },
      {
        id: "march8-2",
        src: "https://picsum.photos/800/600?random=23",
        alt: { bg: "Подаръци за учителките", en: "Gifts for teachers" }
      }
    ]
  },
  {
    id: 25,
    title: {
      bg: "Пожарна",
      en: "Fire Department"
    },
    date: "2018-03-14",
    published: true,
    hits: 1935,
    description: "Посещение на пожарната",
    images: [
      {
        id: "fire-1",
        src: "https://picsum.photos/800/600?random=24",
        alt: { bg: "Пожарна кола", en: "Fire truck" }
      },
      {
        id: "fire-2",
        src: "https://picsum.photos/800/600?random=25",
        alt: { bg: "Ученици с пожарникари", en: "Students with firefighters" }
      },
      {
        id: "fire-3",
        src: "https://picsum.photos/800/600?random=26",
        alt: { bg: "Образователна екскурзия", en: "Educational excursion" }
      }
    ]
  },
  {
    id: 26,
    title: {
      bg: "Маратон на метенето в ДГ",
      en: "Sweeping Marathon in Kindergarten"
    },
    date: "2018-03-14",
    published: true,
    hits: 1959,
    images: [
      {
        id: "sweep-1",
        src: "https://picsum.photos/800/600?random=27",
        alt: { bg: "Деца метат", en: "Children sweeping" }
      },
      {
        id: "sweep-2",
        src: "https://picsum.photos/800/600?random=28",
        alt: { bg: "Почистване на двора", en: "Cleaning the yard" }
      }
    ]
  },
  {
    id: 27,
    title: {
      bg: "23.02.2017",
      en: "February 23, 2017"
    },
    date: "2018-03-14",
    published: true,
    hits: 1905,
    images: [
      {
        id: "feb23-1",
        src: "https://picsum.photos/800/600?random=29",
        alt: { bg: "Februar 2017 събития", en: "February 2017 events" }
      }
    ]
  },
  {
    id: 28,
    title: {
      bg: "Полиция",
      en: "Police"
    },
    date: "2018-03-14",
    published: true,
    hits: 1949,
    description: "Посещение на полицията",
    images: [
      {
        id: "police-1",
        src: "https://picsum.photos/800/600?random=30",
        alt: { bg: "Полицейска кола", en: "Police car" }
      },
      {
        id: "police-2",
        src: "https://picsum.photos/800/600?random=31",
        alt: { bg: "Ученици с полицаи", en: "Students with police officers" }
      },
      {
        id: "police-3",
        src: "https://picsum.photos/800/600?random=32",
        alt: { bg: "Образователна среща", en: "Educational meeting" }
      }
    ]
  }
];

// Helper to get all images from all albums for a flat gallery view
export const getAllGalleryImages = (locale: 'bg' | 'en' = 'bg') => {
  const allImages: Array<{ id: string; src: string; alt: string; album: string }> = [];

  mockGalleryAlbums.forEach(album => {
    album.images.forEach(image => {
      allImages.push({
        id: image.id,
        src: image.src,
        alt: image.alt[locale],
        album: album.title[locale]
      });
    });
  });

  return allImages;
};
