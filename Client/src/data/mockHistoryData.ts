// Mock history data for achievements and directors

export interface Achievement {
  id: number;
  title: string;
  description?: string;
  year?: number;
  position: number;
}

export interface Director {
  id: number;
  name: string;
  tenure_start?: string;
  tenure_end?: string;
  description?: string;
  position: number;
}

export const mockAchievements: Achievement[] = [
  {
    id: 1,
    title: "Първо място в националния конкурс по математика",
    description: "Нашите ученици спечелиха златен медал на националното математическо състезание",
    year: 2024,
    position: 1
  },
  {
    id: 2,
    title: "Награда за най-добра екологична програма",
    description: "Училището получи признание за иновативния си подход към опазване на околната среда",
    year: 2023,
    position: 2
  },
  {
    id: 3,
    title: "Европейско признание за културни проекти",
    description: "Сертификат за участие в международна програма за културен обмен",
    year: 2023,
    position: 3
  },
  {
    id: 4,
    title: "Спортни постижения в областта",
    description: "Златни медали по карате и волейбол на областно ниво",
    year: 2024,
    position: 4
  },
  {
    id: 5,
    title: "100% успеваемост",
    description: "Всички ученици от випуск 2023 успешно завършиха образованието си",
    year: 2023,
    position: 5
  },
  {
    id: 6,
    title: "Модернизация на училището",
    description: "Завършена модернизация на учебните зали с нова технология и оборудване",
    year: 2022,
    position: 6
  },
  {
    id: 7,
    title: "Награда 'Училище на годината'",
    description: "Регионална награда за цялостно развитие и академични постижения",
    year: 2021,
    position: 7
  }
];

export const mockDirectors: Director[] = [
  {
    id: 1,
    name: "Мария Иванова Петрова",
    tenure_start: "2020-09-01",
    tenure_end: undefined, // Currently serving
    description: "Опитен педагог с над 25 години стаж в образованието. Носител на награда 'Учител на годината 2019'",
    position: 1
  },
  {
    id: 2,
    name: "Георги Димитров Стоянов",
    tenure_start: "2015-09-01",
    tenure_end: "2020-08-31",
    description: "Ръководи училището през периода на значителна модернизация и дигитализация",
    position: 2
  },
  {
    id: 3,
    name: "Елена Николова Георгиева",
    tenure_start: "2010-09-01",
    tenure_end: "2015-08-31",
    description: "Заслужен педагог, фокусирана върху развитието на извънкласни дейности",
    position: 3
  },
  {
    id: 4,
    name: "Иван Петров Василев",
    tenure_start: "2005-09-01",
    tenure_end: "2010-08-31",
    description: "Иницииращ пионерски програми по информационни технологии",
    position: 4
  },
  {
    id: 5,
    name: "Стоянка Христова Димитрова",
    tenure_start: "1998-09-01",
    tenure_end: "2005-08-31",
    description: "Дългогодишен директор, положила основите на съвременното училище",
    position: 5
  }
];

// Helper functions with translations
export const getAchievementsInLanguage = (locale: 'bg' | 'en'): Achievement[] => {
  if (locale === 'en') {
    return mockAchievements.map(achievement => ({
      ...achievement,
      title: translateAchievementTitle(achievement.title),
      description: achievement.description ? translateAchievementDescription(achievement.description) : undefined
    }));
  }
  return mockAchievements;
};

export const getDirectorsInLanguage = (locale: 'bg' | 'en'): Director[] => {
  if (locale === 'en') {
    return mockDirectors.map(director => ({
      ...director,
      description: director.description ? translateDirectorDescription(director.description) : undefined
    }));
  }
  return mockDirectors;
};

function translateAchievementTitle(title: string): string {
  const translations: Record<string, string> = {
    "Първо място в националния конкурс по математика": "First Place in National Mathematics Competition",
    "Награда за най-добра екологична програма": "Award for Best Environmental Program",
    "Европейско признание за културни проекти": "European Recognition for Cultural Projects",
    "Спортни постижения в областта": "Regional Sports Achievements",
    "100% успеваемост": "100% Success Rate",
    "Модернизация на училището": "School Modernization",
    "Награда 'Училище на годината'": "'School of the Year' Award"
  };
  return translations[title] || title;
}

function translateAchievementDescription(description: string): string {
  const translations: Record<string, string> = {
    "Нашите ученици спечелиха златен медал на националното математическо състезание": "Our students won gold medal at the national mathematics competition",
    "Училището получи признание за иновативния си подход към опазване на околната среда": "The school received recognition for its innovative approach to environmental protection",
    "Сертификат за участие в международна програма за културен обмен": "Certificate for participation in international cultural exchange program",
    "Златни медали по карате и волейбол на областно ниво": "Gold medals in karate and volleyball at regional level",
    "Всички ученици от випуск 2023 успешно завършиха образованието си": "All students from the 2023 class successfully completed their education",
    "Завършена модернизация на учебните зали с нова технология и оборудване": "Completed modernization of classrooms with new technology and equipment",
    "Регионална награда за цялостно развитие и академични постижения": "Regional award for overall development and academic achievements"
  };
  return translations[description] || description;
}

function translateDirectorDescription(description: string): string {
  const translations: Record<string, string> = {
    "Опитен педагог с над 25 години стаж в образованието. Носител на награда 'Учител на годината 2019'": "Experienced educator with over 25 years in education. Holder of 'Teacher of the Year 2019' award",
    "Ръководи училището през периода на значителна модернизация и дигитализация": "Led the school through a period of significant modernization and digitalization",
    "Заслужен педагог, фокусирана върху развитието на извънкласни дейности": "Distinguished educator, focused on the development of extracurricular activities",
    "Иницииращ пионерски програми по информационни технологии": "Initiated pioneering programs in information technology",
    "Дългогодишен директор, положила основите на съвременното училище": "Long-serving director who laid the foundations of the modern school"
  };
  return translations[description] || description;
}
