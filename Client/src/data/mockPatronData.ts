// Mock patron data for the patron page

export interface PatronContentItem {
  id: number;
  section_key: string;
  title?: string;
  content?: string;
  image_url?: string;
  position: number;
}

export const mockPatronContentBG: PatronContentItem[] = [
  {
    id: 1,
    section_key: 'quote',
    content: '"Образованието е най-мощното оръжие, което можете да използвате, за да промените света." - Нелсън Мандела',
    position: 1
  },
  {
    id: 2,
    section_key: 'biography_p1',
    content: 'Нашето училище носи името на изтъкнатия български просветител и общественик Васил Левски (1837-1873). Известен като Апостолът на свободата, Васил Левски посвети целия си живот на борбата за освобождението на България от османско владичество.',
    position: 2
  },
  {
    id: 3,
    section_key: 'biography_p2',
    title: 'Ранен живот',
    content: 'Роден в град Карлово като Васил Иванов Кунчев, той получава начално образование в родния си град, а по-късно учи в манастира в Сопот. Още от млади години показва извънредни способности и страст към знанието.',
    position: 3
  },
  {
    id: 4,
    section_key: 'biography_p3',
    title: 'Революционна дейност',
    content: 'Васил Левски създава мрежа от революционни комитети из цялата страна, работейки неуморно за организирането на въстание срещу османската власт. Неговата визия беше за свободна и демократична България, където всички граждани биха имали равни права.',
    position: 4
  },
  {
    id: 5,
    section_key: 'biography_p4',
    title: 'Принципи и идеали',
    content: 'Левски вярваше в чистата република и в равенството на всички български граждани, независимо от етническия им произход или религия. Той отхвърляше всякаква чуждестранна намеса и настояваше, че България трябва да бъде освободена чрез собствени усилия.',
    position: 5
  },
  {
    id: 6,
    section_key: 'biography_p5',
    content: 'На 19 февруари 1873 година, Васил Левски беше заловен от османските власти и обесен близо до София. Неговата саможертва и принципност го превърнаха в най-почитаната фигура в българската история. Днес той е символ на свободата, справедливостта и отдадеността на високи идеали.',
    position: 6
  },
  {
    id: 7,
    section_key: 'legacy_title',
    content: 'Наследство и значение за училището',
    position: 7
  },
  {
    id: 8,
    section_key: 'legacy_content',
    content: 'Като носим името на Васил Левски, ние се стремим да възпитаваме в нашите ученици същите ценности, които той отстояваше: свобода, равенство, справедливост и любов към родината. Неговият пример ни вдъхновява да работим за създаването на по-добро бъдеще за нашата страна и да възпитаваме граждани с високи морални принципи и патриотичен дух.',
    position: 8
  },
  {
    id: 9,
    section_key: 'image_main',
    image_url: 'https://picsum.photos/400/500?random=11',
    position: 9
  },
  {
    id: 10,
    section_key: 'image_caption',
    content: 'Васил Левски - Апостолът на свободата',
    position: 10
  }
];

export const mockPatronContentEN: PatronContentItem[] = [
  {
    id: 1,
    section_key: 'quote',
    content: '"Education is the most powerful weapon which you can use to change the world." - Nelson Mandela',
    position: 1
  },
  {
    id: 2,
    section_key: 'biography_p1',
    content: 'Our school bears the name of the prominent Bulgarian educator and public figure Vasil Levski (1837-1873). Known as the Apostle of Freedom, Vasil Levski dedicated his entire life to the struggle for the liberation of Bulgaria from Ottoman rule.',
    position: 2
  },
  {
    id: 3,
    section_key: 'biography_p2',
    title: 'Early Life',
    content: 'Born in the town of Karlovo as Vasil Ivanov Kunchev, he received his primary education in his hometown, and later studied at the monastery in Sopot. From a young age, he showed extraordinary abilities and passion for knowledge.',
    position: 3
  },
  {
    id: 4,
    section_key: 'biography_p3',
    title: 'Revolutionary Activity',
    content: 'Vasil Levski created a network of revolutionary committees throughout the country, working tirelessly to organize an uprising against Ottoman rule. His vision was for a free and democratic Bulgaria, where all citizens would have equal rights.',
    position: 4
  },
  {
    id: 5,
    section_key: 'biography_p4',
    title: 'Principles and Ideals',
    content: 'Levski believed in a pure republic and in the equality of all Bulgarian citizens, regardless of their ethnic origin or religion. He rejected any foreign intervention and insisted that Bulgaria must be liberated through its own efforts.',
    position: 5
  },
  {
    id: 6,
    section_key: 'biography_p5',
    content: 'On February 19, 1873, Vasil Levski was captured by Ottoman authorities and hanged near Sofia. His self-sacrifice and principles made him the most revered figure in Bulgarian history. Today he is a symbol of freedom, justice, and dedication to high ideals.',
    position: 6
  },
  {
    id: 7,
    section_key: 'legacy_title',
    content: 'Legacy and Significance for the School',
    position: 7
  },
  {
    id: 8,
    section_key: 'legacy_content',
    content: 'By bearing the name of Vasil Levski, we strive to instill in our students the same values he upheld: freedom, equality, justice, and love for the homeland. His example inspires us to work towards creating a better future for our country and to educate citizens with high moral principles and patriotic spirit.',
    position: 8
  },
  {
    id: 9,
    section_key: 'image_main',
    image_url: 'https://picsum.photos/400/500?random=11',
    position: 9
  },
  {
    id: 10,
    section_key: 'image_caption',
    content: 'Vasil Levski - The Apostle of Freedom',
    position: 10
  }
];

export const getPatronContentInLanguage = (locale: 'bg' | 'en'): PatronContentItem[] => {
  return locale === 'en' ? mockPatronContentEN : mockPatronContentBG;
};

export const getMockPatronContent = (locale: string = 'bg') => {
  return {
    content: getPatronContentInLanguage(locale === 'bg' ? 'bg' : 'en')
  };
};
