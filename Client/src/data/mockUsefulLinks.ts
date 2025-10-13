export interface MockUsefulLink {
  key: string;
  title: {
    bg: string;
    en: string;
  };
  description?: {
    bg: string;
    en: string;
  };
  url: string;
  cta?: {
    bg: string;
    en: string;
  };
}

export const mockUsefulLinks: MockUsefulLink[] = [
  {
    key: 'mon',
    title: {
      bg: 'Министерство на образованието и науката',
      en: 'Ministry of Education and Science',
    },
    description: {
      bg: 'Официален сайт на МОН с актуална информация за образователната система.',
      en: 'Official MES website with up-to-date information about the education system.',
    },
    url: 'https://www.mon.bg/',
    cta: {
      bg: 'Посети сайта',
      en: 'Visit site',
    },
  },
  {
    key: 'ruo-stara-zagora',
    title: {
      bg: 'Регионално управление на образованието - Стара Загора',
      en: 'Regional Department of Education – Stara Zagora',
    },
    description: {
      bg: 'Информация и новини от РУО, гр. Стара Загора.',
      en: 'Information and news from the regional education authority in Stara Zagora.',
    },
    url: 'https://www.ruo-starazagora.com/',
    cta: {
      bg: 'Посети сайта',
      en: 'Visit site',
    },
  },
  {
    key: 'e-services',
    title: {
      bg: 'Портал за електронни услуги на МОН',
      en: 'MES E-services Portal',
    },
    description: {
      bg: 'Достъп до националната електронна информационна система за предучилищно и училищно образование.',
      en: 'Access to the national electronic information system for preschool and school education.',
    },
    url: 'https://www.e-edu.bg/',
    cta: {
      bg: 'Посети портала',
      en: 'Visit portal',
    },
  },
  {
    key: 'inspection',
    title: {
      bg: 'Национален инспекторат по образованието',
      en: 'National Inspectorate of Education',
    },
    description: {
      bg: 'Информация за инспектиране на училища и детски градини.',
      en: 'Information about inspections of schools and kindergartens.',
    },
    url: 'https://www.mon.bg/bg/100909',
    cta: {
      bg: 'Към инспектората',
      en: 'Go to inspectorate',
    },
  },
  {
    key: 'child-protection',
    title: {
      bg: 'Държавна агенция за закрила на детето',
      en: 'State Agency for Child Protection',
    },
    description: {
      bg: 'Институцията, отговорна за правата и закрилата на децата в България.',
      en: 'The institution responsible for the rights and protection of children in Bulgaria.',
    },
    url: 'https://sacp.government.bg/',
  },
  {
    key: 'safe-internet',
    title: {
      bg: 'Безопасен интернет',
      en: 'Safe Internet',
    },
    description: {
      bg: 'Национален център за безопасен интернет с полезни съвети за деца, родители и учители.',
      en: 'National Safe Internet Center with useful advice for children, parents, and teachers.',
    },
    url: 'https://www.safenet.bg/',
  },
];

