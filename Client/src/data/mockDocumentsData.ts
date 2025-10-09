// Mock documents data extracted from SQL file

export interface DocumentCategory {
  id: string;
  title: {
    bg: string;
    en: string;
  };
  documents: DocumentItem[];
}

export interface DocumentItem {
  id: string;
  title: {
    bg: string;
    en: string;
  };
  filename: string;
  date?: string;
  type: 'pdf' | 'doc' | 'docx' | 'png';
}

export const mockDocuments: DocumentCategory[] = [
  {
    id: 'budget-reports',
    title: {
      bg: 'Отчети на бюджета',
      en: 'Budget Reports'
    },
    documents: [
      {
        id: 'budget-2024-q3',
        title: {
          bg: 'Отчет за трето тримесечие 2024г',
          en: 'Third Quarter Report 2024'
        },
        filename: 'Отчет бюджет 3-тримесечие .pdf',
        date: '2024-09-30',
        type: 'pdf'
      },
      {
        id: 'budget-2024-q2',
        title: {
          bg: 'Отчет за второ тримесечие 2024г',
          en: 'Second Quarter Report 2024'
        },
        filename: 'Отчет 2- тримесечие 2024 г..pdf',
        date: '2024-06-30',
        type: 'pdf'
      },
      {
        id: 'budget-2024-q1',
        title: {
          bg: 'Отчет за първо тримесечие 2024г',
          en: 'First Quarter Report 2024'
        },
        filename: 'отчет 1- тримесечие.pdf',
        date: '2024-03-31',
        type: 'pdf'
      },
      {
        id: 'budget-2023-annual',
        title: {
          bg: 'Годишен отчет бюджет 2023г',
          en: 'Annual Budget Report 2023'
        },
        filename: 'Годишен отчет бюджет 2023.pdf',
        date: '2023-12-31',
        type: 'pdf'
      },
      {
        id: 'budget-2023-q3',
        title: {
          bg: 'Отчет за трето тримесечие 2023г',
          en: 'Third Quarter Report 2023'
        },
        filename: 'отчет на бюджета за 3- тримесечие.pdf',
        date: '2023-09-30',
        type: 'pdf'
      }
    ]
  },
  {
    id: 'rules-plans',
    title: {
      bg: 'Правилници и планове',
      en: 'Rules and Plans'
    },
    documents: [
      {
        id: 'annual-plan-2024',
        title: {
          bg: 'Годишен план за учебната 2024/2025 година',
          en: 'Annual Plan for 2024/2025 School Year'
        },
        filename: 'Годишен план на ОУ Кольо Ганчев, гр Стара Загора за учебната 2024-2025 година.pdf',
        date: '2024-09-01',
        type: 'pdf'
      },
      {
        id: 'school-regulations',
        title: {
          bg: 'Правилник за устройство и дейността',
          en: 'School Organization and Activities Regulations'
        },
        filename: 'Правилник за устройство и дейността на основно училище Кольо Ганчев.pdf',
        type: 'pdf'
      },
      {
        id: 'internal-rules',
        title: {
          bg: 'Правилник за вътрешния трудов ред',
          en: 'Internal Labor Regulations'
        },
        filename: 'Правилник за вътрешния трудов ред.pdf',
        type: 'pdf'
      },
      {
        id: 'education-forms',
        title: {
          bg: 'Форми на обучение за 2024-2025 година',
          en: 'Forms of Education for 2024-2025'
        },
        filename: 'Форми на обучение в основно училище Кольо Ганчев.pdf',
        type: 'pdf'
      },
      {
        id: 'development-strategy',
        title: {
          bg: 'Стратегия за развитие на училището',
          en: 'School Development Strategy'
        },
        filename: 'Стратегия за развитие на училището.pdf',
        type: 'pdf'
      },
      {
        id: 'disaster-protection-plan',
        title: {
          bg: 'План за защита при бедствия',
          en: 'Disaster Protection Plan'
        },
        filename: 'План за защита при бедствия.pdf',
        type: 'pdf'
      },
      {
        id: 'qualification-plan',
        title: {
          bg: 'План за квалификационната дейност',
          en: 'Qualification Activities Plan'
        },
        filename: 'План за квалификационната дейност.pdf',
        type: 'pdf'
      },
      {
        id: 'dropout-prevention',
        title: {
          bg: 'Програма за превенция на отпадането на ученици',
          en: 'Student Dropout Prevention Program'
        },
        filename: 'Програма ЗА ПРЕВЕНЦИЯ НА ОТПАДАНЕТО НА УЧЕНИЦИ.pdf',
        type: 'pdf'
      },
      {
        id: 'road-safety-plan',
        title: {
          bg: 'План за безопасност на движението',
          en: 'Road Safety Plan'
        },
        filename: 'План и програма.pdf',
        type: 'pdf'
      }
    ]
  },
  {
    id: 'admissions',
    title: {
      bg: 'Прием на ученици',
      en: 'Student Admissions'
    },
    documents: [
      {
        id: 'admission-schedule-2025',
        title: {
          bg: 'Заповед график прием 2025-2026',
          en: 'Admission Schedule Order 2025-2026'
        },
        filename: 'Заповед график прием 2025-2026.docx',
        date: '2025-03-01',
        type: 'docx'
      },
      {
        id: 'admission-schedule-2024',
        title: {
          bg: 'График за прием в І клас 2024',
          en: 'First Grade Admission Schedule 2024'
        },
        filename: 'график за дейностите по прим в първи клас за 2024 година , утвърден от кмета на Община Стара Загора.pdf',
        date: '2024-03-01',
        type: 'pdf'
      },
      {
        id: 'admission-plan-approval',
        title: {
          bg: 'Заповед за утвърждаване на училищен план-прием',
          en: 'School Admission Plan Approval Order'
        },
        filename: 'заповед утв на прием.pdf',
        type: 'pdf'
      }
    ]
  },
  {
    id: 'schedules-programs',
    title: {
      bg: 'Графици и разписания',
      en: 'Schedules and Timetables'
    },
    documents: [
      {
        id: 'explained-note-2024',
        title: {
          bg: 'Обяснителна записка към касов отчет 31.12.2024г',
          en: 'Explanatory Note to Cash Report 31.12.2024'
        },
        filename: 'obqsn.zapiska.NU-_2024-12 (1) п..pdf',
        date: '2024-12-31',
        type: 'pdf'
      },
      {
        id: 'explained-note-2025',
        title: {
          bg: 'Обяснителна записка към касов отчет 31.03.2025г',
          en: 'Explanatory Note to Cash Report 31.03.2025'
        },
        filename: 'obqsn.zapiska.NU-_2025-03 п..pdf',
        date: '2025-03-31',
        type: 'pdf'
      },
      {
        id: 'calendar-plan',
        title: {
          bg: 'Календарен план на дейностите',
          en: 'Calendar Plan of Activities'
        },
        filename: 'КАЛЕНДАРЕН ПЛАН НА ДЕЙНОСТИТЕ за изпълнение на основните задачи.pdf',
        type: 'pdf'
      }
    ]
  },
  {
    id: 'competitions',
    title: {
      bg: 'Състезания и олимпиади',
      en: 'Competitions and Olympiads'
    },
    documents: [
      {
        id: 'finance-literacy-2025',
        title: {
          bg: 'Протокол - Национално състезание по Финансова грамотност 2024/2025',
          en: 'Protocol - National Financial Literacy Competition 2024/2025'
        },
        filename: 'приложение.pdf',
        date: '2025-03-15',
        type: 'pdf'
      }
    ]
  }
];

export const getDocumentsByCategory = (categoryId: string): DocumentItem[] => {
  const category = mockDocuments.find(cat => cat.id === categoryId);
  return category ? category.documents : [];
};

export const getAllDocuments = (): DocumentItem[] => {
  return mockDocuments.flatMap(category => category.documents);
};

export const getDocumentById = (id: string): DocumentItem | undefined => {
  for (const category of mockDocuments) {
    const doc = category.documents.find(d => d.id === id);
    if (doc) return doc;
  }
  return undefined;
};

// Helper to generate mock PDF URL (since we don't have actual files)
export const getDocumentUrl = (filename: string): string => {
  // For demonstration, return a placeholder PDF
  // In production, this would point to actual document storage
  return `https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf`;
};
