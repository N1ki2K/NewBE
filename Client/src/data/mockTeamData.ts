// Mock team/staff data

export interface StaffMember {
  id: string;
  name: string;
  role: string;
  imageUrl: string;
  email?: string;
  phone?: string;
  bio?: string;
  is_director?: boolean;
  is_active?: boolean;
  position: number;
}

export const mockStaffMembersBG: StaffMember[] = [
  {
    id: 'director-1',
    name: 'Мария Иванова Петрова',
    role: 'Директор',
    imageUrl: 'https://picsum.photos/400/400?random=20',
    email: 'director@school.bg',
    phone: '+359 888 123 456',
    bio: 'Опитен педагог с над 25 години стаж. Носител на награда "Учител на годината 2019".',
    is_director: true,
    is_active: true,
    position: 1
  },
  {
    id: 'teacher-1',
    name: 'Георги Димитров',
    role: 'Учител по математика',
    imageUrl: 'https://picsum.photos/400/400?random=21',
    email: 'g.dimitrov@school.bg',
    phone: '+359 888 234 567',
    bio: 'Специалист по математика с 15 години опит. Подготвя ученици за национални олимпиади.',
    is_director: false,
    is_active: true,
    position: 2
  },
  {
    id: 'teacher-2',
    name: 'Елена Николова',
    role: 'Учител по български език',
    imageUrl: 'https://picsum.photos/400/400?random=22',
    email: 'e.nikolova@school.bg',
    phone: '+359 888 345 678',
    bio: 'Преподавател по български език и литература. Ръководител на театралния кръжок.',
    is_director: false,
    is_active: true,
    position: 3
  },
  {
    id: 'teacher-3',
    name: 'Иван Петров',
    role: 'Учител по физика',
    imageUrl: 'https://picsum.photos/400/400?random=23',
    email: 'i.petrov@school.bg',
    phone: '+359 888 456 789',
    bio: 'Физик с докторска степен. Ръководител на научния клуб.',
    is_director: false,
    is_active: true,
    position: 4
  },
  {
    id: 'teacher-4',
    name: 'Стоянка Георгиева',
    role: 'Учител по химия',
    imageUrl: 'https://picsum.photos/400/400?random=24',
    email: 's.georgieva@school.bg',
    phone: '+359 888 567 890',
    bio: 'Преподавател по химия и биология. Организатор на екологични проекти.',
    is_director: false,
    is_active: true,
    position: 5
  },
  {
    id: 'teacher-5',
    name: 'Петър Василев',
    role: 'Учител по история',
    imageUrl: 'https://picsum.photos/400/400?random=25',
    email: 'p.vasilev@school.bg',
    phone: '+359 888 678 901',
    bio: 'Историк и археолог. Ръководител на исторически клуб и екскурзии.',
    is_director: false,
    is_active: true,
    position: 6
  },
  {
    id: 'teacher-6',
    name: 'Анна Христова',
    role: 'Учител по английски език',
    imageUrl: 'https://picsum.photos/400/400?random=26',
    email: 'a.hristova@school.bg',
    phone: '+359 888 789 012',
    bio: 'Преподавател по английски език с международна квалификация CELTA.',
    is_director: false,
    is_active: true,
    position: 7
  },
  {
    id: 'teacher-7',
    name: 'Димитър Стоянов',
    role: 'Учител по физическо възпитание',
    imageUrl: 'https://picsum.photos/400/400?random=27',
    email: 'd.stoyanov@school.bg',
    phone: '+359 888 890 123',
    bio: 'Треньор по волейбол и карате. Организатор на спортни събития.',
    is_director: false,
    is_active: true,
    position: 8
  },
  {
    id: 'teacher-8',
    name: 'Надежда Иванова',
    role: 'Учител по музика',
    imageUrl: 'https://picsum.photos/400/400?random=28',
    email: 'n.ivanova@school.bg',
    phone: '+359 888 901 234',
    bio: 'Музикален педагог и диригент на училищния хор.',
    is_director: false,
    is_active: true,
    position: 9
  },
  {
    id: 'teacher-9',
    name: 'Красимир Тодоров',
    role: 'Учител по информационни технологии',
    imageUrl: 'https://picsum.photos/400/400?random=29',
    email: 'k.todorov@school.bg',
    phone: '+359 888 012 345',
    bio: 'IT специалист и програмист. Ръководител на робототехничния клуб.',
    is_director: false,
    is_active: true,
    position: 10
  },
  {
    id: 'teacher-10',
    name: 'Виолета Ангелова',
    role: 'Учител по изобразително изкуство',
    imageUrl: 'https://picsum.photos/400/400?random=30',
    email: 'v.angelova@school.bg',
    phone: '+359 888 123 456',
    bio: 'Художник и преподавател. Организатор на изложби и творчески работилници.',
    is_director: false,
    is_active: true,
    position: 11
  },
  {
    id: 'teacher-11',
    name: 'Николай Колев',
    role: 'Учител по география',
    imageUrl: 'https://picsum.photos/400/400?random=31',
    email: 'n.kolev@school.bg',
    phone: '+359 888 234 567',
    bio: 'Географ и еколог. Водач на планински походи и екскурзии.',
    is_director: false,
    is_active: true,
    position: 12
  }
];

export const mockStaffMembersEN: StaffMember[] = [
  {
    id: 'director-1',
    name: 'Maria Ivanova Petrova',
    role: 'Principal',
    imageUrl: 'https://picsum.photos/400/400?random=20',
    email: 'director@school.bg',
    phone: '+359 888 123 456',
    bio: 'Experienced educator with over 25 years of experience. Holder of "Teacher of the Year 2019" award.',
    is_director: true,
    is_active: true,
    position: 1
  },
  {
    id: 'teacher-1',
    name: 'Georgi Dimitrov',
    role: 'Mathematics Teacher',
    imageUrl: 'https://picsum.photos/400/400?random=21',
    email: 'g.dimitrov@school.bg',
    phone: '+359 888 234 567',
    bio: 'Mathematics specialist with 15 years of experience. Prepares students for national olympiads.',
    is_director: false,
    is_active: true,
    position: 2
  },
  {
    id: 'teacher-2',
    name: 'Elena Nikolova',
    role: 'Bulgarian Language Teacher',
    imageUrl: 'https://picsum.photos/400/400?random=22',
    email: 'e.nikolova@school.bg',
    phone: '+359 888 345 678',
    bio: 'Bulgarian language and literature teacher. Director of the drama club.',
    is_director: false,
    is_active: true,
    position: 3
  },
  {
    id: 'teacher-3',
    name: 'Ivan Petrov',
    role: 'Physics Teacher',
    imageUrl: 'https://picsum.photos/400/400?random=23',
    email: 'i.petrov@school.bg',
    phone: '+359 888 456 789',
    bio: 'Physicist with a doctorate degree. Director of the science club.',
    is_director: false,
    is_active: true,
    position: 4
  },
  {
    id: 'teacher-4',
    name: 'Stoyanka Georgieva',
    role: 'Chemistry Teacher',
    imageUrl: 'https://picsum.photos/400/400?random=24',
    email: 's.georgieva@school.bg',
    phone: '+359 888 567 890',
    bio: 'Chemistry and biology teacher. Organizer of environmental projects.',
    is_director: false,
    is_active: true,
    position: 5
  },
  {
    id: 'teacher-5',
    name: 'Peter Vasilev',
    role: 'History Teacher',
    imageUrl: 'https://picsum.photos/400/400?random=25',
    email: 'p.vasilev@school.bg',
    phone: '+359 888 678 901',
    bio: 'Historian and archaeologist. Director of history club and excursions.',
    is_director: false,
    is_active: true,
    position: 6
  },
  {
    id: 'teacher-6',
    name: 'Anna Hristova',
    role: 'English Teacher',
    imageUrl: 'https://picsum.photos/400/400?random=26',
    email: 'a.hristova@school.bg',
    phone: '+359 888 789 012',
    bio: 'English teacher with CELTA international qualification.',
    is_director: false,
    is_active: true,
    position: 7
  },
  {
    id: 'teacher-7',
    name: 'Dimitar Stoyanov',
    role: 'Physical Education Teacher',
    imageUrl: 'https://picsum.photos/400/400?random=27',
    email: 'd.stoyanov@school.bg',
    phone: '+359 888 890 123',
    bio: 'Volleyball and karate coach. Organizer of sports events.',
    is_director: false,
    is_active: true,
    position: 8
  },
  {
    id: 'teacher-8',
    name: 'Nadezhda Ivanova',
    role: 'Music Teacher',
    imageUrl: 'https://picsum.photos/400/400?random=28',
    email: 'n.ivanova@school.bg',
    phone: '+359 888 901 234',
    bio: 'Music educator and conductor of the school choir.',
    is_director: false,
    is_active: true,
    position: 9
  },
  {
    id: 'teacher-9',
    name: 'Krasimir Todorov',
    role: 'IT Teacher',
    imageUrl: 'https://picsum.photos/400/400?random=29',
    email: 'k.todorov@school.bg',
    phone: '+359 888 012 345',
    bio: 'IT specialist and programmer. Director of the robotics club.',
    is_director: false,
    is_active: true,
    position: 10
  },
  {
    id: 'teacher-10',
    name: 'Violeta Angelova',
    role: 'Art Teacher',
    imageUrl: 'https://picsum.photos/400/400?random=30',
    email: 'v.angelova@school.bg',
    phone: '+359 888 123 456',
    bio: 'Artist and teacher. Organizer of exhibitions and creative workshops.',
    is_director: false,
    is_active: true,
    position: 11
  },
  {
    id: 'teacher-11',
    name: 'Nikolay Kolev',
    role: 'Geography Teacher',
    imageUrl: 'https://picsum.photos/400/400?random=31',
    email: 'n.kolev@school.bg',
    phone: '+359 888 234 567',
    bio: 'Geographer and ecologist. Mountain hiking and excursion guide.',
    is_director: false,
    is_active: true,
    position: 12
  }
];

export const getStaffMembersInLanguage = (locale: 'bg' | 'en'): StaffMember[] => {
  return locale === 'en' ? mockStaffMembersEN : mockStaffMembersBG;
};
