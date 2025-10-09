// Mock events data extracted from localhost.sql
// Based on school events represented in josme_eventgallery_folder table

export interface Event {
  id: number;
  title: string;
  description: string;
  date: string;
  startTime: string;
  endTime: string;
  type: 'academic' | 'extracurricular' | 'meeting' | 'holiday' | 'other';
  location?: string;
}

export const mockEvents: Event[] = [
  {
    id: 1,
    title: "Танцово представление",
    description: "Ученици представят танцови постановки пред родители и гости. Показваме резултатите от работата по танцовата програма.",
    date: "2025-11-15",
    startTime: "10:00",
    endTime: "12:00",
    type: "extracurricular",
    location: "Училищна зала"
  },
  {
    id: 2,
    title: "Усмихнати в розово",
    description: "Благотворително събитие в подкрепа на борбата срещу рака. Всички облечени в розово.",
    date: "2025-10-20",
    startTime: "09:00",
    endTime: "13:00",
    type: "other",
    location: "Училищен двор"
  },
  {
    id: 3,
    title: "24 май - Ден на българската просвета и култура",
    description: "Празник на славянската писменост и култура. Тържествено честване с концерт и награждаване на отличници.",
    date: "2025-05-24",
    startTime: "10:00",
    endTime: "13:00",
    type: "holiday",
    location: "Училищна зала"
  },
  {
    id: 4,
    title: "8 март - Международен ден на жената",
    description: "Празнично честване на 8 март. Ученици подготвят изненади за учителките и съучениците си.",
    date: "2025-03-08",
    startTime: "09:00",
    endTime: "11:00",
    type: "holiday",
    location: "Училище"
  },
  {
    id: 5,
    title: "Посещение в пожарната",
    description: "Образователна екскурзия до местната пожарна станция. Ученици ще научат за работата на пожарникарите и мерките за безопасност.",
    date: "2025-11-08",
    startTime: "10:00",
    endTime: "12:00",
    type: "extracurricular",
    location: "Пожарна станция"
  },
  {
    id: 6,
    title: "Посещение на полицията",
    description: "Среща с местната полиция. Беседа за пътна безопасност и превенция на престъпността.",
    date: "2025-10-25",
    startTime: "11:00",
    endTime: "12:30",
    type: "extracurricular",
    location: "Полицейско управление"
  },
  {
    id: 7,
    title: "Карате тренировка",
    description: "Демонстрация на техники по карате. Отворена тренировка за всички желаещи да се запишат.",
    date: "2025-10-18",
    startTime: "15:00",
    endTime: "17:00",
    type: "extracurricular",
    location: "Спортна зала"
  },
  {
    id: 8,
    title: "Маратон на метенето",
    description: "Доброволческа акция за почистване на училищния двор и околността. Присъединете се към нас!",
    date: "2025-10-12",
    startTime: "09:00",
    endTime: "12:00",
    type: "other",
    location: "Училищен двор"
  },
  {
    id: 9,
    title: "Училищно тържество",
    description: "Годишно училищно тържество с награждаване на отличници и изнасяне на концерт.",
    date: "2025-12-15",
    startTime: "10:00",
    endTime: "13:00",
    type: "academic",
    location: "Училищна зала"
  },
  {
    id: 10,
    title: "Родителска среща",
    description: "Среща на класния ръководител с родителите. Обсъждане на успеха и дисциплината на учениците.",
    date: "2025-10-30",
    startTime: "18:00",
    endTime: "20:00",
    type: "meeting",
    location: "Класна стая"
  },
  {
    id: 11,
    title: "Есенен базар",
    description: "Есенен благотворителен базар организиран от учениците и родителите. Средствата ще бъдат използвани за подобрения в училището.",
    date: "2025-11-01",
    startTime: "10:00",
    endTime: "15:00",
    type: "other",
    location: "Училищен двор"
  },
  {
    id: 12,
    title: "Ден на отворените врати",
    description: "Училището отваря врати за бъдещи ученици и техните родители. Презентация на програми и дейности.",
    date: "2025-12-05",
    startTime: "10:00",
    endTime: "14:00",
    type: "other",
    location: "Училище"
  },
  {
    id: 13,
    title: "Коледен концерт",
    description: "Традиционният коледен концерт с участието на всички класове. Празнична програма за цялото семейство.",
    date: "2025-12-20",
    startTime: "17:00",
    endTime: "19:00",
    type: "extracurricular",
    location: "Училищна зала"
  },
  {
    id: 14,
    title: "Спортен ден",
    description: "Ден на спорта с различни състезания и игри за всички ученици.",
    date: "2025-11-22",
    startTime: "09:00",
    endTime: "15:00",
    type: "extracurricular",
    location: "Стадион"
  },
  {
    id: 15,
    title: "Есенна екскурзия",
    description: "Едноднева екскурзия до природен парк. Образователна програма по биология и екология.",
    date: "2025-10-27",
    startTime: "08:00",
    endTime: "16:00",
    type: "extracurricular",
    location: "Природен парк"
  }
];

// Helper to get events in a specific language
export const getEventsInLanguage = (locale: 'bg' | 'en'): Event[] => {
  if (locale === 'en') {
    // Return English translations
    return mockEvents.map(event => ({
      ...event,
      title: translateTitleToEnglish(event.title),
      description: translateDescriptionToEnglish(event.description)
    }));
  }
  return mockEvents;
};

// Simple translation helpers
function translateTitleToEnglish(title: string): string {
  const translations: Record<string, string> = {
    "Танцово представление": "Dance Performance",
    "Усмихнати в розово": "Smiling in Pink",
    "24 май - Ден на българската просвета и култура": "May 24th - Day of Bulgarian Education and Culture",
    "8 март - Международен ден на жената": "March 8th - International Women's Day",
    "Посещение в пожарната": "Fire Department Visit",
    "Посещение на полицията": "Police Visit",
    "Карате тренировка": "Karate Training",
    "Маратон на метенето": "Sweeping Marathon",
    "Училищно тържество": "School Celebration",
    "Родителска среща": "Parent Meeting",
    "Есенен базар": "Autumn Fair",
    "Ден на отворените врати": "Open House Day",
    "Коледен концерт": "Christmas Concert",
    "Спортен ден": "Sports Day",
    "Есенна екскурзия": "Autumn Excursion"
  };
  return translations[title] || title;
}

function translateDescriptionToEnglish(description: string): string {
  const translations: Record<string, string> = {
    "Ученици представят танцови постановки пред родители и гости. Показваме резултатите от работата по танцовата програма.": "Students present dance performances to parents and guests. Showing results from the dance program.",
    "Благотворително събитие в подкрепа на борбата срещу рака. Всички облечени в розово.": "Charity event in support of the fight against cancer. Everyone dressed in pink.",
    "Празник на славянската писменост и култура. Тържествено честване с концерт и награждаване на отличници.": "Celebration of Slavic literacy and culture. Festive celebration with concert and awards for excellent students.",
    "Празнично честване на 8 март. Ученици подготвят изненади за учителките и съучениците си.": "Holiday celebration of March 8th. Students prepare surprises for teachers and classmates.",
    "Образователна екскурзия до местната пожарна станция. Ученици ще научат за работата на пожарникарите и мерките за безопасност.": "Educational trip to the local fire station. Students will learn about firefighters' work and safety measures.",
    "Среща с местната полиция. Беседа за пътна безопасност и превенция на престъпността.": "Meeting with local police. Talk about road safety and crime prevention.",
    "Демонстрация на техники по карате. Отворена тренировка за всички желаещи да се запишат.": "Demonstration of karate techniques. Open training for all who wish to join.",
    "Доброволческа акция за почистване на училищния двор и околността. Присъединете се към нас!": "Volunteer action to clean the school yard and surroundings. Join us!",
    "Годишно училищно тържество с награждаване на отличници и изнасяне на концерт.": "Annual school celebration with awards for excellent students and a concert.",
    "Среща на класния ръководител с родителите. Обсъждане на успеха и дисциплината на учениците.": "Class teacher meeting with parents. Discussion of students' success and discipline.",
    "Есенен благотворителен базар организиран от учениците и родителите. Средствата ще бъдат използвани за подобрения в училището.": "Autumn charity fair organized by students and parents. Funds will be used for school improvements.",
    "Училището отваря врати за бъдещи ученици и техните родители. Презентация на програми и дейности.": "School opens doors for future students and their parents. Presentation of programs and activities.",
    "Традиционният коледен концерт с участието на всички класове. Празнична програма за цялото семейство.": "Traditional Christmas concert with all classes participating. Festive program for the whole family.",
    "Ден на спорта с различни състезания и игри за всички ученици.": "Sports day with various competitions and games for all students.",
    "Едноднева екскурзия до природен парк. Образователна програма по биология и екология.": "One-day excursion to a nature park. Educational program in biology and ecology."
  };
  return translations[description] || description;
}

// Helper to filter upcoming events
export const getUpcomingEvents = (limit?: number): Event[] => {
  const now = new Date();
  const upcoming = mockEvents
    .filter(event => new Date(event.date) >= now)
    .sort((a, b) => new Date(a.date).getTime() - new Date(b.date).getTime());

  return limit ? upcoming.slice(0, limit) : upcoming;
};
