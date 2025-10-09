// Mock news articles data

export interface NewsArticle {
  id: string;
  title: string;
  excerpt: string;
  content: string;
  featured_image_url?: string;
  published_date: string;
  is_published: boolean;
  is_featured: boolean;
}

export const mockNewsArticlesBG: NewsArticle[] = [
  {
    id: 'news-1',
    title: 'Ученици от нашето училище спечелиха златен медал на националната олимпиада по математика',
    excerpt: 'Гордеем се да съобщим, че нашите ученици постигнаха блестящ успех на националното състезание по математика.',
    content: `<p>С голяма радост съобщаваме, че ученици от нашето училище спечелиха златен медал на националната олимпиада по математика, проведена този месец в София.</p>

<p>Иван Петров от 10 клас и Мария Георгиева от 9 клас представиха отлични решения на сложни математически задачи и се класираха на първо място в своите категории.</p>

<p>"Това е резултат от упоритата работа на нашите ученици и отдадеността на нашия екип от учители по математика," коментира директорът на училището Мария Петрова.</p>

<p>Състезанието събра над 200 ученици от цялата страна, което прави постижението на нашите представители още по-значимо.</p>

<p>Поздравяваме нашите победители и им желаем още много успехи!</p>`,
    featured_image_url: 'https://picsum.photos/800/600?random=40',
    published_date: '2025-09-15',
    is_published: true,
    is_featured: true
  },
  {
    id: 'news-2',
    title: 'Нова компютърна лаборатория беше открита в училището',
    excerpt: 'Днес официално бе открита новата компютърна лаборатория, оборудвана с модерна техника.',
    content: `<p>След месеци на подготовка и ремонтни дейности, днес официално бе открита новата компютърна лаборатория в нашето училище.</p>

<p>Лабораторията разполага с 30 нови компютъра, интерактивна дъска и специализиран софтуер за обучение по програмиране, графичен дизайн и робототехника.</p>

<p>"Тази инвестиция е част от нашата стратегия за модернизация на образователния процес и подготовка на учениците за дигиталната ера," каза заместник-директорът Георги Димитров.</p>

<p>Лабораторията ще се използва както за редовни часове по информационни технологии, така и за извънкласни дейности и работа по проекти.</p>

<p>Финансирането за проекта беше осигурено чрез европейска програма и дарения от местния бизнес.</p>`,
    featured_image_url: 'https://picsum.photos/800/600?random=41',
    published_date: '2025-09-10',
    is_published: true,
    is_featured: true
  },
  {
    id: 'news-3',
    title: 'Започна новата учебна година с тържествена церемония',
    excerpt: 'На 15 септември отново отворихме врати за нашите ученици с празничен първи учебен ден.',
    content: `<p>Учебната 2025/2026 година започна с традиционна тържествена церемония на 15 септември.</p>

<p>Събитието започна с поздравителни речи от директора на училището и кметът на общината, последвани от художествена програма, подготвена от нашите талантливи ученици.</p>

<p>Специално внимание бе отделено на първокласниците, които за първи път преминаха прага на училището.</p>

<p>"Желаем на всички ученици успешна и плодотворна учебна година, пълна с нови знания, приятелства и незабравими моменти," каза директорът Мария Петрова.</p>

<p>След церемонията всички класове проведоха първия си час, където класните ръководители представиха плановете за новата година.</p>`,
    featured_image_url: 'https://picsum.photos/800/600?random=42',
    published_date: '2025-09-15',
    is_published: true,
    is_featured: true
  },
  {
    id: 'news-4',
    title: 'Екологичен проект на училището получи национална награда',
    excerpt: 'Нашата иницитива за разделно събиране на отпадъци и рециклиране беше отличена с престижна награда.',
    content: `<p>Екологичният проект на нашето училище "Зелено бъдеще" спечели първо място в националния конкурс за екологични инициативи в образованието.</p>

<p>Проектът включва програма за разделно събиране на отпадъци, създаване на училищна градина и образователни семинари по опазване на околната среда.</p>

<p>Над 200 ученици взеха активно участие в различните дейности на проекта през изминалата година.</p>

<p>"Това признание е доказателство, че когато работим заедно за важна кауза, можем да постигнем изключителни резултати," коментира координаторът на проекта Стоянка Георгиева.</p>

<p>Наградата включва финансиране от 10,000 лева за разширяване на проекта през следващата година.</p>`,
    featured_image_url: 'https://picsum.photos/800/600?random=43',
    published_date: '2025-08-20',
    is_published: true,
    is_featured: false
  },
  {
    id: 'news-5',
    title: 'Ученици представиха България на международен форум в Брюксел',
    excerpt: 'Делегация от нашето училище участва в престижен европейски младежки форум.',
    content: `<p>Група от петима ученици от нашето училище представиха България на Европейския младежки форум в Брюксел, Белгия.</p>

<p>Форумът събра над 500 ученици от 28 европейски държави за дискусии на теми като климатични промени, дигитализация и младежка заетост.</p>

<p>Нашите представители презентираха проект за устойчиво развитие в малките градове, който бе високо оценен от международното жури.</p>

<p>"Това беше невероятно преживяване. Имахме възможност да споделим нашите идеи и да научим много от младежи от други страни," сподели участничката Елена Иванова.</p>

<p>Участието в форума беше финансирано от програма "Еразъм+".</p>`,
    featured_image_url: 'https://picsum.photos/800/600?random=44',
    published_date: '2025-07-10',
    is_published: true,
    is_featured: false
  },
  {
    id: 'news-6',
    title: 'Лятна творческа школа с рекорден брой участници',
    excerpt: 'Над 100 ученици взеха участие в лятната програма на училището.',
    content: `<p>Лятната творческа школа, организирана от нашето училище, завърши с рекорден брой участници - над 100 деца.</p>

<p>Програмата включваше разнообразни занимания - изкуство, музика, спорт, робототехника и чужди езици.</p>

<p>Всеки ден бе изпълнен с интересни дейности, творчески работилници и екскурзии.</p>

<p>"Радваме се, че успяхме да предложим качествена и интересна програма за децата през лятната ваканция," каза организаторът Виолета Ангелова.</p>

<p>В края на програмата беше организирана изложба с най-добрите творби на участниците.</p>`,
    featured_image_url: 'https://picsum.photos/800/600?random=45',
    published_date: '2025-08-25',
    is_published: true,
    is_featured: false
  },
  {
    id: 'news-7',
    title: 'Нов спортен комплекс ще бъде построен до училището',
    excerpt: 'Общината одобри проект за изграждане на модерен спортен комплекс.',
    content: `<p>Общинският съвет одобри проект за изграждане на модерен спортен комплекс в непосредствена близост до училището.</p>

<p>Комплексът ще включва футболно игрище, тенис кортове, фитнес зала и площадка за стрийт спорт.</p>

<p>Строителството ще започне през пролетта на 2026 година и се очаква да завърши до края на същата година.</p>

<p>"Това е страхотна новина за нашите ученици. Спортните съоръжения ще позволят развитието на различни спортни дейности," коментира учителят по физическо възпитание Димитър Стоянов.</p>

<p>Финансирането е осигурено от Министерството на младежта и спорта.</p>`,
    featured_image_url: 'https://picsum.photos/800/600?random=46',
    published_date: '2025-06-15',
    is_published: true,
    is_featured: false
  },
  {
    id: 'news-8',
    title: 'Коледен благотворителен базар подкрепи деца в нужда',
    excerpt: 'Традиционният коледен базар събра средства за благотворителни каузи.',
    content: `<p>Традиционният коледен благотворителен базар на училището се състоя в началото на декември и събра рекордната сума от 5,000 лева.</p>

<p>Учениците, родителите и учителите подготвиха ръчно изработени подаръци, сладкиши и коледни украси, които бяха предложени на посетителите.</p>

<p>Събраните средства ще бъдат дарени на фондация, която подкрепя деца от социално слаби семейства.</p>

<p>"Благодарим на всички, които се включиха в тази прекрасна инициатива. Коледа е време за споделяне и подкрепа," каза директорът Мария Петрова.</p>`,
    featured_image_url: 'https://picsum.photos/800/600?random=47',
    published_date: '2024-12-10',
    is_published: true,
    is_featured: false
  }
];

export const mockNewsArticlesEN: NewsArticle[] = [
  {
    id: 'news-1',
    title: 'Students from our school won gold medal at the national mathematics olympiad',
    excerpt: 'We are proud to announce that our students achieved brilliant success at the national mathematics competition.',
    content: `<p>We are delighted to announce that students from our school won a gold medal at the national mathematics olympiad held this month in Sofia.</p>

<p>Ivan Petrov from 10th grade and Maria Georgieva from 9th grade presented excellent solutions to complex mathematical problems and ranked first in their categories.</p>

<p>"This is the result of the hard work of our students and the dedication of our mathematics teaching team," commented the school principal Maria Petrova.</p>

<p>The competition gathered over 200 students from across the country, making our representatives' achievement even more significant.</p>

<p>We congratulate our winners and wish them many more successes!</p>`,
    featured_image_url: 'https://picsum.photos/800/600?random=40',
    published_date: '2025-09-15',
    is_published: true,
    is_featured: true
  },
  {
    id: 'news-2',
    title: 'New computer lab was opened at the school',
    excerpt: 'Today, the new computer laboratory, equipped with modern technology, was officially opened.',
    content: `<p>After months of preparation and renovation work, the new computer laboratory at our school was officially opened today.</p>

<p>The laboratory has 30 new computers, an interactive board, and specialized software for teaching programming, graphic design, and robotics.</p>

<p>"This investment is part of our strategy to modernize the educational process and prepare students for the digital era," said Deputy Principal Georgi Dimitrov.</p>

<p>The laboratory will be used for both regular IT classes and extracurricular activities and project work.</p>

<p>Funding for the project was provided through a European program and donations from local businesses.</p>`,
    featured_image_url: 'https://picsum.photos/800/600?random=41',
    published_date: '2025-09-10',
    is_published: true,
    is_featured: true
  },
  {
    id: 'news-3',
    title: 'The new school year started with a ceremonial event',
    excerpt: 'On September 15, we once again opened our doors to our students with a festive first day of school.',
    content: `<p>The 2025/2026 school year began with a traditional ceremonial event on September 15.</p>

<p>The event started with welcoming speeches from the school principal and the mayor, followed by an artistic program prepared by our talented students.</p>

<p>Special attention was given to first graders who crossed the school threshold for the first time.</p>

<p>"We wish all students a successful and fruitful school year, full of new knowledge, friendships, and unforgettable moments," said Principal Maria Petrova.</p>

<p>After the ceremony, all classes held their first lesson, where class teachers presented plans for the new year.</p>`,
    featured_image_url: 'https://picsum.photos/800/600?random=42',
    published_date: '2025-09-15',
    is_published: true,
    is_featured: true
  },
  {
    id: 'news-4',
    title: 'School environmental project received national award',
    excerpt: 'Our initiative for waste separation and recycling was honored with a prestigious award.',
    content: `<p>Our school's environmental project "Green Future" won first place in the national competition for environmental initiatives in education.</p>

<p>The project includes a program for waste separation, creation of a school garden, and educational seminars on environmental protection.</p>

<p>Over 200 students actively participated in various project activities over the past year.</p>

<p>"This recognition is proof that when we work together for an important cause, we can achieve exceptional results," commented project coordinator Stoyanka Georgieva.</p>

<p>The award includes funding of 10,000 BGN to expand the project next year.</p>`,
    featured_image_url: 'https://picsum.photos/800/600?random=43',
    published_date: '2025-08-20',
    is_published: true,
    is_featured: false
  },
  {
    id: 'news-5',
    title: 'Students represented Bulgaria at international forum in Brussels',
    excerpt: 'A delegation from our school participated in a prestigious European youth forum.',
    content: `<p>A group of five students from our school represented Bulgaria at the European Youth Forum in Brussels, Belgium.</p>

<p>The forum brought together over 500 students from 28 European countries for discussions on topics such as climate change, digitalization, and youth employment.</p>

<p>Our representatives presented a project on sustainable development in small towns, which was highly appreciated by the international jury.</p>

<p>"This was an incredible experience. We had the opportunity to share our ideas and learn a lot from young people from other countries," shared participant Elena Ivanova.</p>

<p>Participation in the forum was funded by the "Erasmus+" program.</p>`,
    featured_image_url: 'https://picsum.photos/800/600?random=44',
    published_date: '2025-07-10',
    is_published: true,
    is_featured: false
  },
  {
    id: 'news-6',
    title: 'Summer creative school with record number of participants',
    excerpt: 'Over 100 students participated in the school\'s summer program.',
    content: `<p>The summer creative school organized by our school ended with a record number of participants - over 100 children.</p>

<p>The program included various activities - art, music, sports, robotics, and foreign languages.</p>

<p>Each day was filled with interesting activities, creative workshops, and excursions.</p>

<p>"We are pleased that we were able to offer a quality and interesting program for children during the summer vacation," said organizer Violeta Angelova.</p>

<p>At the end of the program, an exhibition was organized with the best works of the participants.</p>`,
    featured_image_url: 'https://picsum.photos/800/600?random=45',
    published_date: '2025-08-25',
    is_published: true,
    is_featured: false
  },
  {
    id: 'news-7',
    title: 'New sports complex will be built next to the school',
    excerpt: 'The municipality approved a project to build a modern sports complex.',
    content: `<p>The municipal council approved a project to build a modern sports complex in close proximity to the school.</p>

<p>The complex will include a football field, tennis courts, a fitness gym, and a street sports area.</p>

<p>Construction will begin in the spring of 2026 and is expected to be completed by the end of the same year.</p>

<p>"This is great news for our students. The sports facilities will allow the development of various sports activities," commented physical education teacher Dimitar Stoyanov.</p>

<p>Funding is provided by the Ministry of Youth and Sports.</p>`,
    featured_image_url: 'https://picsum.photos/800/600?random=46',
    published_date: '2025-06-15',
    is_published: true,
    is_featured: false
  },
  {
    id: 'news-8',
    title: 'Christmas charity fair supported children in need',
    excerpt: 'The traditional Christmas fair raised funds for charitable causes.',
    content: `<p>The school's traditional Christmas charity fair took place in early December and raised a record amount of 5,000 BGN.</p>

<p>Students, parents, and teachers prepared handmade gifts, sweets, and Christmas decorations that were offered to visitors.</p>

<p>The funds raised will be donated to a foundation that supports children from socially disadvantaged families.</p>

<p>"We thank everyone who participated in this wonderful initiative. Christmas is a time for sharing and support," said Principal Maria Petrova.</p>`,
    featured_image_url: 'https://picsum.photos/800/600?random=47',
    published_date: '2024-12-10',
    is_published: true,
    is_featured: false
  }
];

export const getNewsInLanguage = (locale: 'bg' | 'en', publishedOnly: boolean = true): NewsArticle[] => {
  const articles = locale === 'en' ? mockNewsArticlesEN : mockNewsArticlesBG;
  return publishedOnly ? articles.filter(article => article.is_published) : articles;
};

export const getFeaturedNews = (locale: 'bg' | 'en', limit: number = 3): NewsArticle[] => {
  const articles = getNewsInLanguage(locale, true);
  return articles.filter(article => article.is_featured).slice(0, limit);
};

export const getNewsById = (id: string, locale: 'bg' | 'en'): NewsArticle | undefined => {
  const articles = locale === 'en' ? mockNewsArticlesEN : mockNewsArticlesBG;
  return articles.find(article => article.id === id);
};
