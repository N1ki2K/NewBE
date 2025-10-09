// Mock council data

export interface CouncilMember {
  id: number;
  name: string;
  role: string;
  position: number;
}

export const mockCouncilBG = {
  intro: 'Училищното настоятелство е консултативен орган, който подпомага директора в управлението на училището и осигурява участието на обществото в развитието на образователния процес.',
  functionsTitle: 'Функции на училищното настоятелство',
  functions: [
    'Предлага политики за развитие на училището',
    'Подпомага набирането на допълнителни финансови средства',
    'Съдейства за създаване на условия за качествено образование',
    'Участва в решаването на основни въпроси от дейността на училището',
    'Съдейства за привличане на дарения и спонсорство'
  ],
  membersTitle: 'Членове на настоятелството',
  chairman: {
    role: 'Председател',
    name: 'Петър Димитров Иванов'
  },
  members: [
    {
      id: 1,
      name: 'Мария Георгиева Петрова',
      role: 'Член',
      position: 1
    },
    {
      id: 2,
      name: 'Иван Стоянов Василев',
      role: 'Член',
      position: 2
    },
    {
      id: 3,
      name: 'Елена Христова Николова',
      role: 'Член',
      position: 3
    },
    {
      id: 4,
      name: 'Георги Тодоров Симеонов',
      role: 'Член',
      position: 4
    }
  ],
  contact: 'За въпроси и предложения към училищното настоятелство можете да се свържете с нас на електронна поща: council@school.bg или по телефон: +359 888 999 000'
};

export const mockCouncilEN = {
  intro: 'The School Board is an advisory body that assists the principal in managing the school and ensures public participation in the development of the educational process.',
  functionsTitle: 'Functions of the School Board',
  functions: [
    'Proposes policies for school development',
    'Assists in raising additional financial resources',
    'Helps create conditions for quality education',
    'Participates in resolving key issues of school activities',
    'Assists in attracting donations and sponsorships'
  ],
  membersTitle: 'Board Members',
  chairman: {
    role: 'Chairman',
    name: 'Peter Dimitrov Ivanov'
  },
  members: [
    {
      id: 1,
      name: 'Maria Georgieva Petrova',
      role: 'Member',
      position: 1
    },
    {
      id: 2,
      name: 'Ivan Stoyanov Vasilev',
      role: 'Member',
      position: 2
    },
    {
      id: 3,
      name: 'Elena Hristova Nikolova',
      role: 'Member',
      position: 3
    },
    {
      id: 4,
      name: 'Georgi Todorov Simeonov',
      role: 'Member',
      position: 4
    }
  ],
  contact: 'For questions and suggestions to the School Board, you can contact us by email: council@school.bg or by phone: +359 888 999 000'
};

export const getCouncilDataInLanguage = (locale: 'bg' | 'en') => {
  return locale === 'en' ? mockCouncilEN : mockCouncilBG;
};
