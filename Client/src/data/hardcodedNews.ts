/**
 * Hardcoded News Data
 * This file contains news articles that are loaded when the backend is unavailable
 */

export interface NewsArticle {
  id: string;
  title_bg: string;
  title_en: string;
  excerpt_bg: string;
  excerpt_en: string;
  content_bg: string;
  content_en: string;
  featured_image_url: string;
  featured_image_alt: string;
  is_published: boolean;
  is_featured: boolean;
  published_date: string;
  created_at: string;
  updated_at: string;
  attachment_url?: string;
  attachment_name?: string;
}

export const hardcodedNewsData: NewsArticle[] = [
  {
    id: "news-001",
    title_bg: "Заповед график прием 2025-2026",
    title_en: "Order schedule admission 2025-2026",
    excerpt_bg: "Заповед за график на приема на ученици за учебната 2025-2026 година",
    excerpt_en: "Order for the schedule of student admission for the 2025-2026 school year",
    content_bg: "<h2>Заповед график прием 2025-2026</h2><p>Заповед за утвърждаване на график за прием на ученици в първи клас за учебната 2025-2026 година.</p><p>Документът съдържа подробна информация за дейностите, сроковете и процедурите за прием на ученици.</p>",
    content_en: "<h2>Order schedule admission 2025-2026</h2><p>Order for approval of the schedule for admission of students in first grade for the 2025-2026 school year.</p><p>The document contains detailed information about activities, deadlines and procedures for student admission.</p>",
    featured_image_url: "",
    featured_image_alt: "",
    is_published: true,
    is_featured: true,
    published_date: "2025-10-02T00:00:00.000Z",
    created_at: "2025-10-02T14:25:00.000Z",
    updated_at: "2025-10-02T14:25:00.000Z",
    attachment_url: "/Заповед график прием 2025-2026(1).docx",
    attachment_name: "Заповед график прием 2025-2026(1).docx"
  },
  {
    id: "news-002",
    title_bg: "Прием на ученици",
    title_en: "Student Admission",
    excerpt_bg: "График за дейностите по прием в първи клас за 2024 година, утвърден от кмета на Община Стара Загора",
    excerpt_en: "Schedule of activities for admission in first grade for 2024, approved by the Mayor of Stara Zagora Municipality",
    content_bg: "<h2>Прием на ученици</h2><p>График за дейностите по прием в първи клас за 2024 година, утвърден от кмета на Община Стара Загора.</p><p>В документа са посочени всички важни дати и дейности свързани с приема на ученици в първи клас.</p>",
    content_en: "<h2>Student Admission</h2><p>Schedule of activities for admission in first grade for 2024, approved by the Mayor of Stara Zagora Municipality.</p><p>The document specifies all important dates and activities related to the admission of students in first grade.</p>",
    featured_image_url: "",
    featured_image_alt: "",
    is_published: true,
    is_featured: true,
    published_date: "2025-10-02T00:00:00.000Z",
    created_at: "2025-10-02T14:25:00.000Z",
    updated_at: "2025-10-02T14:25:00.000Z",
    attachment_url: "/график за дейностите по прим в първи клас за 2024 година , утвърден от кмета на Община Стара Загора.pdf",
    attachment_name: "график за дейностите по прим в първи клас за 2024 година.pdf"
  },
  {
    id: "news-003",
    title_bg: "Заповед за утвърждаване на Училищен план-прием за ученици в І и V клас на учебната 2025/2026 г.",
    title_en: "Order for approval of School admission plan for students in grades I and V for the 2025/2026 school year",
    excerpt_bg: "Заповед за утвърждаване на училищен план за прием на ученици в първи и пети клас",
    excerpt_en: "Order for approval of school plan for admission of students in first and fifth grade",
    content_bg: "<h2>Заповед за утвърждаване на Училищен план-прием</h2><p>Заповед за утвърждаване на Училищен план-прием за ученици в І и V клас на учебната 2025/2026 г.</p><p>Документът определя условията и реда за прием на ученици в началния и прогимназиалния етап на обучение.</p>",
    content_en: "<h2>Order for approval of School admission plan</h2><p>Order for approval of School admission plan for students in grades I and V for the 2025/2026 school year.</p><p>The document defines the conditions and procedure for admission of students in the primary and lower secondary stages of education.</p>",
    featured_image_url: "",
    featured_image_alt: "",
    is_published: true,
    is_featured: true,
    published_date: "2025-10-02T00:00:00.000Z",
    created_at: "2025-10-02T14:25:00.000Z",
    updated_at: "2025-10-02T14:25:00.000Z",
    attachment_url: "/заповед утв на прием.pdf",
    attachment_name: "заповед утв на прием.pdf"
  },
  {
    id: "news-004",
    title_bg: "Протокол с резултатите от областен кръг на Национално състезание по Финансова грамотност 2024/2025г.",
    title_en: "Protocol with the results from the regional round of the National Financial Literacy Competition 2024/2025",
    excerpt_bg: "Резултати от областния кръг на Национално състезание по финансова грамотност",
    excerpt_en: "Results from the regional round of the National Financial Literacy Competition",
    content_bg: "<h2>Протокол с резултатите от областен кръг</h2><p>Протокол с резултатите от областен кръг на Национално състезание по Финансова грамотност 2024/2025г.</p><p>В документа са публикувани класирането и резултатите на учениците, участвали в областния кръг на състезанието.</p>",
    content_en: "<h2>Protocol with results from regional round</h2><p>Protocol with the results from the regional round of the National Financial Literacy Competition 2024/2025.</p><p>The document publishes the ranking and results of students who participated in the regional round of the competition.</p>",
    featured_image_url: "",
    featured_image_alt: "",
    is_published: true,
    is_featured: false,
    published_date: "2025-10-02T00:00:00.000Z",
    created_at: "2025-10-02T14:25:00.000Z",
    updated_at: "2025-10-02T14:25:00.000Z",
    attachment_url: "/приложение.pdf",
    attachment_name: "приложение.pdf"
  },
  {
    id: "news-005",
    title_bg: "Национално състезание по финансова грамотност през учебната 2024/2025г. Областен кръг",
    title_en: "National Financial Literacy Competition during the 2024/2025 school year. Regional round",
    excerpt_bg: "Информация за Национално състезание по финансова грамотност - областен кръг",
    excerpt_en: "Information about the National Financial Literacy Competition - regional round",
    content_bg: "<h2>Национално състезание по финансова грамотност</h2><p>Национално състезание по финансова грамотност през учебната 2024/2025г. - Областен кръг.</p><p>Информация за организацията, условията и реда за провеждане на областния кръг на състезанието по финансова грамотност.</p>",
    content_en: "<h2>National Financial Literacy Competition</h2><p>National Financial Literacy Competition during the 2024/2025 school year - Regional round.</p><p>Information about the organization, conditions and procedure for conducting the regional round of the financial literacy competition.</p>",
    featured_image_url: "",
    featured_image_alt: "",
    is_published: true,
    is_featured: false,
    published_date: "2025-10-02T00:00:00.000Z",
    created_at: "2025-10-02T14:25:00.000Z",
    updated_at: "2025-10-02T14:25:00.000Z",
    attachment_url: "/приложение.pdf",
    attachment_name: "приложение.pdf"
  }
];

/**
 * Get news articles filtered by language and publication status
 */
export function getNewsArticles(language: string = 'bg', publishedOnly: boolean = true): any[] {
  const isEnglish = language === 'en';

  let articles = hardcodedNewsData;

  if (publishedOnly) {
    articles = articles.filter(article => article.is_published);
  }

  return articles.map(article => ({
    id: article.id,
    title: isEnglish ? (article.title_en || article.title_bg) : (article.title_bg || article.title_en),
    excerpt: isEnglish ? (article.excerpt_en || article.excerpt_bg) : (article.excerpt_bg || article.excerpt_en),
    content: isEnglish ? (article.content_en || article.content_bg) : (article.content_bg || article.content_en),
    featured_image_url: article.featured_image_url,
    published_date: article.published_date,
    is_published: article.is_published,
    is_featured: article.is_featured,
    attachment_url: article.attachment_url,
    attachment_name: article.attachment_name
  }));
}

/**
 * Get featured news articles
 */
export function getFeaturedArticles(language: string = 'bg', limit: number = 3): any[] {
  const articles = getNewsArticles(language, true);
  return articles.filter(article => article.is_featured).slice(0, limit);
}

/**
 * Get a single news article by ID
 */
export function getArticleById(id: string, language: string = 'bg'): any | undefined {
  const articles = getNewsArticles(language, false);
  return articles.find(article => article.id === id);
}
