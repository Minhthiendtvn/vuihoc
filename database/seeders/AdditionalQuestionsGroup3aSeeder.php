<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdditionalQuestionsGroup3aSeeder extends Seeder
{
    private array $lessonBySlug = [];
    private array $orderByLesson = [];
    private array $countCache = [];

    private function lesson(string $slug): \App\Models\Lesson {
        if (!isset($this->lessonBySlug[$slug])) {
            $this->lessonBySlug[$slug] = \App\Models\Lesson::where('slug', $slug)->firstOrFail();
        }
        return $this->lessonBySlug[$slug];
    }
    private function qCount(string $lessonSlug, string $type): int {
        $key = $lessonSlug . ':' . $type;
        if (!isset($this->countCache[$key])) {
            $this->countCache[$key] = \App\Models\Question::where('lesson_id', $this->lesson($lessonSlug)->id)->where('game_type', $type)->count();
        }
        return $this->countCache[$key];
    }
    private function bumpCount(string $lessonSlug, string $type): void { $this->countCache[$lessonSlug . ':' . $type]++; }
    private function promptExists(string $lessonSlug, string $type, string $prompt): bool {
        return \App\Models\Question::where('lesson_id', $this->lesson($lessonSlug)->id)->where('game_type', $type)->where('prompt', $prompt)->exists();
    }
    private function newQuestion(string $lessonSlug, string $gameType, string $prompt, string $explanation, string $difficulty = 'de'): \App\Models\Question {
        $lesson = $this->lesson($lessonSlug);
        if (!isset($this->orderByLesson[$lessonSlug])) {
            $this->orderByLesson[$lessonSlug] = (int) \App\Models\Question::where('lesson_id', $lesson->id)->max('sort_order');
        }
        $this->orderByLesson[$lessonSlug]++;
        return \App\Models\Question::create([
            'lesson_id' => $lesson->id, 'game_type' => $gameType, 'prompt' => $prompt,
            'explanation' => $explanation, 'difficulty' => $difficulty, 'points' => 10,
            'sort_order' => $this->orderByLesson[$lessonSlug],
            'grade' => $lesson->grade, 'is_demo' => true,
        ]);
    }
    private function quiz(string $lesson, string $prompt, array $options, int $correct, string $explanation, string $difficulty = 'de'): void {
        if ($this->qCount($lesson, 'quiz') >= 8 || $this->promptExists($lesson, 'quiz', $prompt)) return;
        $q = $this->newQuestion($lesson, 'quiz', $prompt, $explanation, $difficulty);
        foreach ($options as $i => $text) { \App\Models\QuestionOption::create(['question_id' => $q->id, 'option_text' => $text, 'is_correct' => $i === $correct, 'sort_order' => $i + 1]); }
        $this->bumpCount($lesson, 'quiz');
    }
    private function matching(string $lesson, string $prompt, array $pairs, string $explanation, string $difficulty = 'de'): void {
        if ($this->qCount($lesson, 'matching') >= 8 || $this->promptExists($lesson, 'matching', $prompt)) return;
        $q = $this->newQuestion($lesson, 'matching', $prompt, $explanation, $difficulty);
        foreach ($pairs as $i => [$left, $right]) { \App\Models\MatchingPair::create(['question_id' => $q->id, 'left_text' => $left, 'right_text' => $right, 'sort_order' => $i + 1]); }
        $this->bumpCount($lesson, 'matching');
    }
    private function sortQ(string $lesson, string $prompt, array $items, string $explanation, string $difficulty = 'de'): void {
        if ($this->qCount($lesson, 'sort') >= 8 || $this->promptExists($lesson, 'sort', $prompt)) return;
        $q = $this->newQuestion($lesson, 'sort', $prompt, $explanation, $difficulty);
        foreach ($items as $i => [$text, $category]) { \App\Models\SortItem::create(['question_id' => $q->id, 'item_text' => $text, 'category' => $category, 'sort_order' => $i + 1]); }
        $this->bumpCount($lesson, 'sort');
    }
    private function fill(string $lesson, string $prompt, array $answers, string $explanation, string $difficulty = 'de'): void {
        if ($this->qCount($lesson, 'fill') >= 8 || $this->promptExists($lesson, 'fill', $prompt)) return;
        if (!str_contains($prompt, '___')) return;
        $q = $this->newQuestion($lesson, 'fill', $prompt, $explanation, $difficulty);
        foreach ($answers as $i => [$blankIndex, $text]) { \App\Models\FillAnswer::create(['question_id' => $q->id, 'blank_index' => $blankIndex, 'answer_text' => $text, 'sort_order' => $i + 1]); }
        $this->bumpCount($lesson, 'fill');
    }

    public function run(): void
    {
        $this->seedEnMyFamily();
        $this->seedEnAtSchool();
        $this->seedEnPresentSimple();
        $this->seedEnPrepositions();
        $this->seedEnHealth();
        $this->seedEnTravel();
        $this->seedEnFamilyVocab();
        $this->seedEnSchoolVocab();
        $this->seedEnDailyLife();
        $this->seedEnWeather();
        $this->seedEnToBe();
        $this->seedEnPresentSimple2();
        $this->seedEnNegQuestion();
        $this->seedEnPresentContinuous();
        $this->seedEnHealthVocab2();
        $this->seedEnTravelVocab2();
        $this->seedEnEnvironment();
        $this->seedEnCommunity();
        $this->seedEnTenseReview1();
        $this->seedEnTenseReview2();
        $this->seedEnConditional1a();
        $this->seedEnConditional1b();
        $this->seedEnPassive1();
        $this->seedEnPassive2();
        $this->seedEnReported1();
        $this->seedEnReported2();
        $this->seedEnRelative1();
        $this->seedEnRelative2();
        $this->seedEnInversion1();
        $this->seedEnInversion2();
    }

    // 1. en-my-family (g6, de) - Family vocabulary
    private function seedEnMyFamily(): void
    {
        $L = 'en-my-family'; $d = 'de';
        $this->quiz($L, 'What is "mẹ" in English?', ['mother', 'father', 'sister', 'brother'], 0, '“Mẹ” in English is “mother”. Father = bố.', $d);
        $this->quiz($L, 'What is "anh trai" in English?', ['father', 'mother', 'brother', 'sister'], 2, '“Anh trai” is “brother”. Sister = chị/em gái.', $d);
        $this->quiz($L, 'What is "bà" in English?', ['aunt', 'grandmother', 'sister', 'mother'], 1, '“Bà” is “grandmother”. Grandfather = ông.', $d);
        $this->quiz($L, 'What is "con gái" in English?', ['father', 'daughter', 'son', 'mother'], 1, '“Con gái” is “daughter”. Son = con trai.', $d);
        $this->quiz($L, 'What is "chú (cậu)" in English?', ['aunt', 'cousin', 'father', 'uncle'], 3, '“Chú/cậu” is “uncle”. Aunt = cô/dì.', $d);
        $this->matching($L, 'Match each family word with its Vietnamese meaning (set A).', [['mother', 'mẹ'], ['father', 'bố'], ['son', 'con trai'], ['daughter', 'con gái']], 'mother = mẹ, father = bố, son = con trai, daughter = con gái.', $d);
        $this->matching($L, 'Match each family word with its Vietnamese meaning (set B).', [['brother', 'anh/em trai'], ['sister', 'chị/em gái'], ['uncle', 'chú/cậu'], ['aunt', 'cô/dì']], 'brother = anh/em trai, sister = chị/em gái, uncle = chú/cậu, aunt = cô/dì.', $d);
        $this->matching($L, 'Match each family word with its Vietnamese meaning (set C).', [['grandfather', 'ông'], ['grandmother', 'bà'], ['cousin', 'anh/chị/em họ'], ['parents', 'bố mẹ']], 'grandfather = ông, grandmother = bà, cousin = anh/chị/em họ, parents = bố mẹ.', $d);
        $this->matching($L, 'Match each sentence with its Vietnamese meaning (family).', [['This is my mother.', 'Đây là mẹ tôi.'], ['He is my brother.', 'Anh ấy là anh trai tôi.'], ['She is my aunt.', 'Cô ấy là dì tôi.'], ['They are my parents.', 'Họ là bố mẹ tôi.']], 'Read the whole sentence to get the meaning right.', $d);
        $this->matching($L, 'Match each family word with its Vietnamese meaning (set D).', [['baby', 'em bé'], ['family', 'gia đình'], ['wife', 'vợ'], ['husband', 'chồng']], 'baby = em bé, family = gia đình, wife = vợ, husband = chồng.', $d);
        $this->sortQ($L, 'Drag each word into MALE or FEMALE.', [['father', 'MALE'], ['mother', 'FEMALE'], ['brother', 'MALE'], ['sister', 'FEMALE'], ['uncle', 'MALE'], ['aunt', 'FEMALE']], 'father, brother, uncle are male; mother, sister, aunt are female.', $d);
        $this->sortQ($L, 'Drag each word into PARENTS or CHILDREN.', [['father', 'PARENTS'], ['mother', 'PARENTS'], ['son', 'CHILDREN'], ['daughter', 'CHILDREN']], 'Parents = bố mẹ; children = con cái.', $d);
        $this->sortQ($L, 'Drag each word into SIBLINGS or GRANDPARENTS.', [['brother', 'SIBLINGS'], ['sister', 'SIBLINGS'], ['grandfather', 'GRANDPARENTS'], ['grandmother', 'GRANDPARENTS']], 'Siblings = anh chị em; grandparents = ông bà.', $d);
        $this->sortQ($L, 'Drag each word into CLOSE FAMILY or EXTENDED FAMILY.', [['mother', 'CLOSE FAMILY'], ['father', 'CLOSE FAMILY'], ['uncle', 'EXTENDED FAMILY'], ['aunt', 'EXTENDED FAMILY']], 'Close family = gia đình nhỏ (bố mẹ, anh chị em).', $d);
        $this->sortQ($L, 'Drag each word into YOUR GENERATION or OLDER GENERATION.', [['brother', 'YOUR GENERATION'], ['sister', 'YOUR GENERATION'], ['father', 'OLDER GENERATION'], ['mother', 'OLDER GENERATION']], 'Siblings are your generation; parents are older.', $d);
        $this->fill($L, 'My ___ is a teacher. (“Mẹ” của tôi là giáo viên.)', [[0, 'mother']], '“Mẹ” in English is “mother”.', $d);
        $this->fill($L, 'His ___ plays football very well. (“Anh trai” của anh ấy đá bóng rất giỏi.)', [[0, 'brother']], '“Anh trai” is “brother”.', $d);
        $this->fill($L, '“Bà” in English is ___.', [[0, 'grandmother']], '“Bà” = grandmother; “ông” = grandfather.', $d);
        $this->fill($L, 'We love our ___. (“Ông bà” của chúng tôi.)', [[0, 'grandparents']], '“Ông bà” = grandparents.', $d);
        $this->fill($L, 'Her ___ is tall and kind. (“Chị gái” của cô ấy cao và tốt bụng.)', [[0, 'sister']], '“Chị gái” is “sister”.', $d);
    }

    // 2. en-at-school (g6, de) - At school vocabulary
    private function seedEnAtSchool(): void
    {
        $L = 'en-at-school'; $d = 'de';
        $this->quiz($L, 'What is "giáo viên" in English?', ['teacher', 'student', 'book', 'ruler'], 0, '“Giáo viên” is “teacher”. Student = học sinh.', $d);
        $this->quiz($L, 'What is "quyển vở" in English?', ['pen', 'notebook', 'pencil', 'bag'], 1, '“Quyển vở” is “notebook”.', $d);
        $this->quiz($L, 'What is "thước kẻ" in English?', ['book', 'pen', 'ruler', 'eraser'], 2, '“Thước kẻ” is “ruler”.', $d);
        $this->quiz($L, 'What is "cục tẩy" in English?', ['pencil', 'pen', 'book', 'eraser'], 3, '“Cục tẩy” is “eraser”.', $d);
        $this->quiz($L, 'Which of these is something you read?', ['pen', 'book', 'desk', 'chair'], 1, 'You read a book. You write with a pen.', $d);
        $this->matching($L, 'Match each school word with its Vietnamese meaning (set 1).', [['teacher', 'giáo viên'], ['student', 'học sinh'], ['book', 'sách'], ['pen', 'bút mực']], 'teacher = giáo viên, student = học sinh, book = sách, pen = bút mực.', $d);
        $this->matching($L, 'Match each school word with its Vietnamese meaning (set 2).', [['pencil', 'bút chì'], ['ruler', 'thước kẻ'], ['eraser', 'cục tẩy'], ['bag', 'cặp sách']], 'pencil = bút chì, ruler = thước kẻ, eraser = cục tẩy, bag = cặp sách.', $d);
        $this->matching($L, 'Match each school word with its Vietnamese meaning (set 3).', [['classroom', 'lớp học'], ['library', 'thư viện'], ['blackboard', 'bảng đen'], ['desk', 'bàn học']], 'classroom = lớp học, library = thư viện, blackboard = bảng đen, desk = bàn học.', $d);
        $this->matching($L, 'Match each subject with its Vietnamese meaning.', [['Maths', 'Toán'], ['English', 'Tiếng Anh'], ['Music', 'Âm nhạc'], ['Art', 'Mỹ thuật']], 'Maths = Toán, English = Tiếng Anh, Music = Âm nhạc, Art = Mỹ thuật.', $d);
        $this->matching($L, 'Match each school word with its Vietnamese meaning (set 4).', [['school', 'trường học'], ['lesson', 'bài học'], ['homework', 'bài tập về nhà'], ['exam', 'bài kiểm tra']], 'school = trường học, lesson = bài học, homework = bài tập về nhà, exam = bài kiểm tra.', $d);
        $this->sortQ($L, 'Drag each word into THINGS YOU WRITE WITH or THINGS YOU READ.', [['pencil', 'THINGS YOU WRITE WITH'], ['pen', 'THINGS YOU WRITE WITH'], ['book', 'THINGS YOU READ'], ['newspaper', 'THINGS YOU READ']], 'You write with a pen or pencil; you read a book or newspaper.', $d);
        $this->sortQ($L, 'Drag each subject into LANGUAGE or NUMBER.', [['English', 'LANGUAGE'], ['Vietnamese', 'LANGUAGE'], ['Maths', 'NUMBER'], ['Science', 'NUMBER']], 'Languages = ngôn ngữ; Maths and Science use numbers.', $d);
        $this->sortQ($L, 'Drag each word into PERSON or THING.', [['teacher', 'PERSON'], ['student', 'PERSON'], ['book', 'THING'], ['pen', 'THING']], 'Teacher and student are people; book and pen are things.', $d);
        $this->sortQ($L, 'Drag each item into IN YOUR PENCIL CASE or NOT.', [['pencil', 'IN YOUR PENCIL CASE'], ['pen', 'IN YOUR PENCIL CASE'], ['eraser', 'IN YOUR PENCIL CASE'], ['book', 'NOT']], 'A pencil case holds small things like pens and erasers.', $d);
        $this->sortQ($L, 'Drag each place into INSIDE THE SCHOOL or OUTSIDE.', [['classroom', 'INSIDE THE SCHOOL'], ['library', 'INSIDE THE SCHOOL'], ['playground', 'INSIDE THE SCHOOL'], ['street', 'OUTSIDE']], 'The playground is inside the school yard.', $d);
        $this->fill($L, 'The ___ writes on the blackboard. (Giáo viên viết lên bảng.)', [[0, 'teacher']], 'The teacher writes on the blackboard.', $d);
        $this->fill($L, 'We do our ___ at home every evening. (Chúng tôi làm bài tập về nhà mỗi tối.)', [[0, 'homework']], '“Bài tập về nhà” is “homework”.', $d);
        $this->fill($L, 'Maths is my favourite ___. (Môn Toán là môn học yêu thích của tôi.)', [[0, 'subject']], '“Môn học” is “subject”.', $d);
        $this->fill($L, 'The students are in the ___. (Các học sinh đang ở trong lớp học.)', [[0, 'classroom']], '“Lớp học” is “classroom”.', $d);
        $this->fill($L, 'I borrow books from the ___. (Tôi mượn sách ở thư viện.)', [[0, 'library']], '“Thư viện” is “library”.', $d);
    }

    // 3. en-present-simple (g6, trung_binh) - Present simple tense
    private function seedEnPresentSimple(): void
    {
        $L = 'en-present-simple'; $d = 'trung_binh';
        $this->quiz($L, 'We ___ breakfast at 6 a.m.', ['have', 'has', 'having', 'haves'], 0, 'With I/you/we/they, use the base verb: we have.', $d);
        $this->quiz($L, 'My dad ___ in a bank.', ['work', 'works', 'working', 'is work'], 1, 'Third person singular (he/she/it) adds -s: works.', $d);
        $this->quiz($L, 'Cats ___ milk.', ['like', 'likes', 'liking', 'is like'], 0, 'Plural subject “cats” takes the base verb: like.', $d);
        $this->quiz($L, 'She ___ her teeth twice a day.', ['brush', 'brushes', 'brushing', 'brushs'], 1, 'After -sh, third person singular adds -es: brushes.', $d);
        $this->quiz($L, 'They ___ to music every evening.', ['listens', 'listen', 'listening', 'are listen'], 1, 'With “they”, use the base verb: listen.', $d);
        $this->matching($L, 'Match each subject with the correct form of "study" (set B).', [['I', 'study'], ['She', 'studies'], ['They', 'study'], ['He', 'studies']], 'I/you/we/they + study; he/she/it + studies.', $d);
        $this->matching($L, 'Match each verb with its third-person singular form.', [['go', 'goes'], ['watch', 'watches'], ['fly', 'flies'], ['play', 'plays']], 'go→goes, watch→watches, fly→flies, play→plays.', $d);
        $this->matching($L, 'Match each frequency adverb with its Vietnamese meaning.', [['always', 'luôn luôn'], ['usually', 'thường xuyên'], ['sometimes', 'thỉnh thoảng'], ['never', 'không bao giờ']], 'always = luôn luôn, usually = thường xuyên, sometimes = thỉnh thoảng, never = không bao giờ.', $d);
        $this->matching($L, 'Match each sentence start with the correct ending.', [['She', 'plays tennis'], ['They', 'watch TV'], ['He', 'reads books'], ['We', 'like music']], 'She plays, they watch, he reads, we like.', $d);
        $this->matching($L, 'Match each question with the correct short answer.', [['Does she swim?', 'Yes, she does.'], ['Do they run?', 'Yes, they do.'], ['Does he sing?', 'No, he doesn\'t.'], ['Do we play?', 'Yes, we do.']], 'Does↔she/he, do↔they/we in short answers.', $d);
        $this->sortQ($L, 'Drag each sentence into ADDS -S or NO -S.', [['She runs.', 'ADDS -S'], ['He eats.', 'ADDS -S'], ['They run.', 'NO -S'], ['We eat.', 'NO -S']], 'He/she/it adds -s; I/you/we/they do not.', $d);
        $this->sortQ($L, 'Drag each verb into WITH -ES or WITH -S.', [['watches', 'WITH -ES'], ['goes', 'WITH -ES'], ['runs', 'WITH -S'], ['plays', 'WITH -S']], 'Verbs ending in -o, -sh, -ch, -ss, -x add -es.', $d);
        $this->sortQ($L, 'Drag each word into SUBJECT or VERB.', [['she', 'SUBJECT'], ['they', 'SUBJECT'], ['go', 'VERB'], ['play', 'VERB']], 'Subjects do the action; verbs are the action.', $d);
        $this->sortQ($L, 'Drag each sentence into HABIT or FACT.', [['I get up at 6.', 'HABIT'], ['She plays tennis on Sundays.', 'HABIT'], ['The sun rises in the east.', 'FACT'], ['Water boils at 100°C.', 'FACT']], 'Present simple shows habits and general truths.', $d);
        $this->sortQ($L, 'Drag each sentence into PRESENT SIMPLE or NOT.', [['He works here.', 'PRESENT SIMPLE'], ['They like rice.', 'PRESENT SIMPLE'], ['He is working now.', 'NOT'], ['They liked rice.', 'NOT']], 'Present simple uses the base verb or verb + s/es.', $d);
        $this->fill($L, 'She ___ (play) the piano every day.', [[0, 'plays']], 'Third person singular: she plays.', $d);
        $this->fill($L, 'They ___ (go) to the park on Sundays.', [[0, 'go']], 'With “they”, use the base verb: go.', $d);
        $this->fill($L, 'My mother ___ (cook) dinner at 6 p.m.', [[0, 'cooks']], 'My mother = she → cooks.', $d);
        $this->fill($L, 'The dog ___ (bark) at night.', [[0, 'barks']], 'The dog = it → barks.', $d);
        $this->fill($L, 'We ___ (not/watch) TV in the morning.', [[0, 'do not watch']], 'Negative with “we”: do not + base verb.', $d);
    }

    // 4. en-prepositions (g6, de) - Prepositions of place
    private function seedEnPrepositions(): void
    {
        $L = 'en-prepositions'; $d = 'de';
        $this->quiz($L, 'The book is ___ the table.', ['on', 'in', 'under', 'behind'], 0, '“On” = trên bề mặt: on the table.', $d);
        $this->quiz($L, 'The keys are ___ the bag.', ['on', 'in', 'under', 'next to'], 1, '“In” = bên trong: in the bag.', $d);
        $this->quiz($L, 'The ball is ___ the chair.', ['on', 'in', 'under', 'between'], 2, '“Under” = phía dưới: under the chair.', $d);
        $this->quiz($L, 'She sits ___ her friend.', ['on', 'behind', 'in', 'next to'], 3, '“Next to” = bên cạnh: next to her friend.', $d);
        $this->quiz($L, 'The clock hangs ___ the wall.', ['on', 'in', 'under', 'above'], 0, 'Pictures and clocks hang “on” the wall.', $d);
        $this->matching($L, 'Match each preposition with its Vietnamese meaning (set 1).', [['on', 'trên'], ['in', 'trong'], ['under', 'dưới'], ['behind', 'đằng sau']], 'on = trên, in = trong, under = dưới, behind = đằng sau.', $d);
        $this->matching($L, 'Match each preposition with its Vietnamese meaning (set 2).', [['next to', 'bên cạnh'], ['between', 'ở giữa'], ['in front of', 'phía trước'], ['above', 'phía trên']], 'next to = bên cạnh, between = ở giữa, in front of = phía trước, above = phía trên.', $d);
        $this->matching($L, 'Match each sentence with its Vietnamese meaning.', [['The cat is on the chair.', 'Con mèo ở trên ghế.'], ['The dog is under the table.', 'Con chó ở dưới gầm bàn.'], ['She is behind the door.', 'Cô ấy ở sau cánh cửa.'], ['He is next to me.', 'Anh ấy ở bên cạnh tôi.']], 'Read the preposition to find the right meaning.', $d);
        $this->matching($L, 'Match each phrase with its Vietnamese meaning.', [['in the box', 'trong hộp'], ['on the desk', 'trên bàn'], ['under the bed', 'dưới gầm giường'], ['behind the tree', 'sau gốc cây']], 'in the box = trong hộp, on the desk = trên bàn, under the bed = dưới gầm giường.', $d);
        $this->matching($L, 'Match each question with the correct answer.', [['Where is the pen?', 'It is on the book.'], ['Where is the cat?', 'It is under the chair.'], ['Where is she?', 'She is behind me.'], ['Where is the ball?', 'It is in the box.']], '“Where” asks about place; answer with a preposition phrase.', $d);
        $this->sortQ($L, 'Drag each sentence into the correct preposition: IN, ON or AT.', [['I live in Hanoi.', 'IN'], ['The book is on the desk.', 'ON'], ['We meet at 7 a.m.', 'AT'], ['She is in the room.', 'IN']], 'in = trong, on = trên, at = tại (thời điểm/địa điểm cụ thể).', $d);
        $this->sortQ($L, 'Drag each phrase into IN FRONT OF or BEHIND.', [['in front of the house', 'IN FRONT OF'], ['in front of the class', 'IN FRONT OF'], ['behind the door', 'BEHIND'], ['behind the tree', 'BEHIND']], 'In front of = phía trước; behind = phía sau.', $d);
        $this->sortQ($L, 'Drag each phrase into NEXT TO or BETWEEN.', [['next to me', 'NEXT TO'], ['next to the school', 'NEXT TO'], ['between two houses', 'BETWEEN'], ['between A and B', 'BETWEEN']], 'Next to = bên cạnh (một vật); between = ở giữa (hai vật).', $d);
        $this->sortQ($L, 'Drag each statement about prepositions into TRUE or FALSE.', [['“On” means “trên”.', 'TRUE'], ['“Under” means “trên”.', 'FALSE'], ['“Behind” means “đằng sau”.', 'TRUE'], ['“In” means “dưới”.', 'FALSE']], 'on = trên, under = dưới, behind = đằng sau, in = trong.', $d);
        $this->sortQ($L, 'Drag each phrase into IN or ON.', [['in the garden', 'IN'], ['in the bag', 'IN'], ['on the wall', 'ON'], ['on the table', 'ON']], 'in the garden/bag, on the wall/table.', $d);
        $this->fill($L, 'We have lunch ___ noon. (Chúng tôi ăn trưa lúc 12 giờ.)', [[0, 'at']], '“At” dùng với giờ cụ thể: at noon.', $d);
        $this->fill($L, 'The shoes are ___ the bed. (Đôi giày ở dưới gầm giường.)', [[0, 'under']], '“Under” = phía dưới.', $d);
        $this->fill($L, 'He stands ___ the door. (Anh ấy đứng sau cánh cửa.)', [[0, 'behind']], '“Behind” = đằng sau.', $d);
        $this->fill($L, 'The bank is ___ the post office and the school. (Ngân hàng ở giữa bưu điện và trường học.)', [[0, 'between']], '“Between” = ở giữa hai vật.', $d);
        $this->fill($L, 'My birthday is ___ May. (Sinh nhật tôi vào tháng 5.)', [[0, 'in']], '“In” dùng với tháng/năm: in May.', $d);
    }

    // 5. en-health (g7, de) - Health vocabulary
    private function seedEnHealth(): void
    {
        $L = 'en-health'; $d = 'de';
        $this->quiz($L, 'What is "cảm lạnh" in English?', ['a cold', 'a fever', 'a cough', 'a headache'], 0, '“Cảm lạnh” is “a cold”.', $d);
        $this->quiz($L, 'What is "ho" in English?', ['sneeze', 'cough', 'fever', 'flu'], 1, '“Ho” is “cough”.', $d);
        $this->quiz($L, 'What is "thuốc" in English?', ['hospital', 'doctor', 'medicine', 'nurse'], 2, '“Thuốc” is “medicine”.', $d);
        $this->quiz($L, 'What is "y tá" in English?', ['patient', 'nurse', 'clinic', 'pill'], 1, '“Y tá” is “nurse”. Doctor = bác sĩ.', $d);
        $this->quiz($L, 'What is "đau bụng" in English?', ['a stomachache', 'a toothache', 'a backache', 'an earache'], 0, '“Đau bụng” is “a stomachache”.', $d);
        $this->matching($L, 'Match each health word with its Vietnamese meaning (set A).', [['headache', 'đau đầu'], ['fever', 'sốt'], ['cold', 'cảm lạnh'], ['cough', 'ho']], 'headache = đau đầu, fever = sốt, cold = cảm lạnh, cough = ho.', $d);
        $this->matching($L, 'Match each health word with its Vietnamese meaning (set B).', [['flu', 'cúm'], ['toothache', 'đau răng'], ['stomachache', 'đau bụng'], ['backache', 'đau lưng']], 'flu = cúm, toothache = đau răng, stomachache = đau bụng, backache = đau lưng.', $d);
        $this->matching($L, 'Match each health word with its Vietnamese meaning (set C).', [['doctor', 'bác sĩ'], ['nurse', 'y tá'], ['hospital', 'bệnh viện'], ['medicine', 'thuốc']], 'doctor = bác sĩ, nurse = y tá, hospital = bệnh viện, medicine = thuốc.', $d);
        $this->matching($L, 'Match each health word with its Vietnamese meaning (set D).', [['healthy', 'khỏe mạnh'], ['sick', 'ốm'], ['tired', 'mệt'], ['weak', 'yếu']], 'healthy = khỏe mạnh, sick = ốm, tired = mệt, weak = yếu.', $d);
        $this->matching($L, 'Match each health word with its Vietnamese meaning (set E).', [['exercise', 'tập thể dục'], ['rest', 'nghỉ ngơi'], ['sleep', 'ngủ'], ['eat vegetables', 'ăn rau']], 'exercise = tập thể dục, rest = nghỉ ngơi, sleep = ngủ.', $d);
        $this->sortQ($L, 'Drag each word into SYMPTOM or BODY PART.', [['headache', 'SYMPTOM'], ['fever', 'SYMPTOM'], ['arm', 'BODY PART'], ['leg', 'BODY PART']], 'Symptoms = triệu chứng; body parts = bộ phận cơ thể.', $d);
        $this->sortQ($L, 'Drag each advice into GOOD or BAD for your health.', [['Drink water.', 'GOOD'], ['Eat vegetables.', 'GOOD'], ['Smoke cigarettes.', 'BAD'], ['Stay up late.', 'BAD']], 'Water and vegetables are good; smoking and late nights are bad.', $d);
        $this->sortQ($L, 'Drag each word into PERSON or PLACE.', [['doctor', 'PERSON'], ['nurse', 'PERSON'], ['hospital', 'PLACE'], ['clinic', 'PLACE']], 'Doctor and nurse are people; hospital and clinic are places.', $d);
        $this->sortQ($L, 'Drag each word into HEALTHY HABIT or UNHEALTHY HABIT.', [['exercise', 'HEALTHY HABIT'], ['eat fruit', 'HEALTHY HABIT'], ['drink soda', 'UNHEALTHY HABIT'], ['play games all night', 'UNHEALTHY HABIT']], 'Exercise and fruit are healthy; soda and all-night games are not.', $d);
        $this->sortQ($L, 'Drag each word into ILLNESS or MEDICINE.', [['flu', 'ILLNESS'], ['cold', 'ILLNESS'], ['pill', 'MEDICINE'], ['syrup', 'MEDICINE']], 'Flu and cold are illnesses; pills and syrup are medicines.', $d);
        $this->fill($L, 'I have a ___. I need some aspirin. (Tôi bị đau đầu.)', [[0, 'headache']], '“Đau đầu” is “headache”.', $d);
        $this->fill($L, 'She has a ___. Her body is very hot. (Cô ấy bị sốt.)', [[0, 'fever']], '“Sốt” is “fever”.', $d);
        $this->fill($L, 'You should see a ___ when you are sick. (Bạn nên đi khám bác sĩ khi ốm.)', [[0, 'doctor']], '“Bác sĩ” is “doctor”.', $d);
        $this->fill($L, 'Drink lots of water and get some ___. (Hãy nghỉ ngơi.)', [[0, 'rest']], '“Nghỉ ngơi” is “rest”.', $d);
        $this->fill($L, '___ keeps you strong and healthy. (Tập thể dục giúp bạn khỏe mạnh.)', [[0, 'Exercise']], '“Tập thể dục” is “exercise”.', $d);
    }

    // 6. en-travel (g7, de) - Travel vocabulary
    private function seedEnTravel(): void
    {
        $L = 'en-travel'; $d = 'de';
        $this->quiz($L, 'What is "hộ chiếu" in English?', ['passport', 'ticket', 'visa', 'luggage'], 0, '“Hộ chiếu” is “passport”.', $d);
        $this->quiz($L, 'What is "hành lý" in English?', ['suitcase', 'luggage', 'backpack', 'ticket'], 1, '“Hành lý” is “luggage”.', $d);
        $this->quiz($L, 'What is "hướng dẫn viên du lịch" in English?', ['pilot', 'driver', 'tour guide', 'waiter'], 2, '“Hướng dẫn viên du lịch” is “tour guide”.', $d);
        $this->quiz($L, 'What is "bản đồ" in English?', ['ticket', 'camera', 'guidebook', 'map'], 3, '“Bản đồ” is “map”.', $d);
        $this->quiz($L, 'What is "đặt phòng" in English?', ['to cancel', 'to book', 'to travel', 'to fly'], 1, '“Đặt phòng” is “to book (a room)”.', $d);
        $this->matching($L, 'Match each travel word with its Vietnamese meaning (set A).', [['ticket', 'vé'], ['passport', 'hộ chiếu'], ['hotel', 'khách sạn'], ['beach', 'bãi biển']], 'ticket = vé, passport = hộ chiếu, hotel = khách sạn, beach = bãi biển.', $d);
        $this->matching($L, 'Match each travel word with its Vietnamese meaning (set B).', [['airport', 'sân bay'], ['station', 'nhà ga'], ['luggage', 'hành lý'], ['map', 'bản đồ']], 'airport = sân bay, station = nhà ga, luggage = hành lý, map = bản đồ.', $d);
        $this->matching($L, 'Match each vehicle with its Vietnamese meaning.', [['plane', 'máy bay'], ['train', 'tàu hỏa'], ['bus', 'xe buýt'], ['ship', 'tàu thủy']], 'plane = máy bay, train = tàu hỏa, bus = xe buýt, ship = tàu thủy.', $d);
        $this->matching($L, 'Match each travel verb with its Vietnamese meaning.', [['travel', 'du lịch'], ['visit', 'thăm'], ['book', 'đặt'], ['pack', 'đóng gói hành lý']], 'travel = du lịch, visit = thăm, book = đặt, pack = đóng gói.', $d);
        $this->matching($L, 'Match each travel sentence with its Vietnamese meaning.', [['We travel by plane.', 'Chúng tôi đi du lịch bằng máy bay.'], ['I booked a hotel.', 'Tôi đã đặt khách sạn.'], ['She packs her suitcase.', 'Cô ấy đóng gói vali.'], ['They visit Ha Long Bay.', 'Họ thăm vịnh Hạ Long.']], 'Read the verb to understand each sentence.', $d);
        $this->sortQ($L, 'Drag each word into TRANSPORT or ACCOMMODATION.', [['plane', 'TRANSPORT'], ['train', 'TRANSPORT'], ['hotel', 'ACCOMMODATION'], ['hostel', 'ACCOMMODATION']], 'Transport = phương tiện; accommodation = nơi ở.', $d);
        $this->sortQ($L, 'Drag each word into DOCUMENT or THING TO PACK.', [['passport', 'DOCUMENT'], ['visa', 'DOCUMENT'], ['clothes', 'THING TO PACK'], ['camera', 'THING TO PACK']], 'Documents = giấy tờ; pack clothes and cameras in your bag.', $d);
        $this->sortQ($L, 'Drag each word into LAND TRANSPORT or WATER TRANSPORT.', [['bus', 'LAND TRANSPORT'], ['car', 'LAND TRANSPORT'], ['ship', 'WATER TRANSPORT'], ['boat', 'WATER TRANSPORT']], 'Bus and car go on land; ship and boat go on water.', $d);
        $this->sortQ($L, 'Drag each activity into AT THE BEACH or IN THE CITY.', [['swim', 'AT THE BEACH'], ['sunbathe', 'AT THE BEACH'], ['visit museums', 'IN THE CITY'], ['go shopping', 'IN THE CITY']], 'Swim at the beach; visit museums in the city.', $d);
        $this->sortQ($L, 'Drag each word into VERB or NOUN.', [['travel', 'VERB'], ['pack', 'VERB'], ['ticket', 'NOUN'], ['luggage', 'NOUN']], 'Travel and pack are actions (verbs); ticket and luggage are things (nouns).', $d);
        $this->fill($L, 'We travel by ___. (Chúng tôi đi du lịch bằng máy bay.)', [[0, 'plane']], '“Máy bay” is “plane”.', $d);
        $this->fill($L, 'Show your ___ at the airport. (Hãy xuất trình hộ chiếu ở sân bay.)', [[0, 'passport']], '“Hộ chiếu” is “passport”.', $d);
        $this->fill($L, 'I ___ a room for two nights. (Tôi đã đặt phòng cho hai đêm.)', [[0, 'booked']], '“Đặt phòng” = book a room (past: booked).', $d);
        $this->fill($L, 'Don\'t forget to ___ your bag. (Đừng quên đóng gói túi xách.)', [[0, 'pack']], '“Đóng gói” is “pack”.', $d);
        $this->fill($L, 'The ___ took us around the old town. (Hướng dẫn viên đưa chúng tôi đi quanh phố cổ.)', [[0, 'tour guide']], '“Hướng dẫn viên” is “tour guide”.', $d);
    }

    // 7. en-tu-vung-lop-6-lop-6-1 (g6, de) - Từ vựng gia đình
    private function seedEnFamilyVocab(): void
    {
        $L = 'en-tu-vung-lop-6-lop-6-1'; $d = 'de';
        $this->quiz($L, '"Brother" nghĩa là gì?', ['anh/em trai', 'chị/em gái', 'bố', 'mẹ'], 0, '"Brother" = anh/em trai.', $d);
        $this->quiz($L, '"Grandmother" nghĩa là gì?', ['ông', 'bà', 'mẹ', 'dì'], 1, '"Grandmother" = bà.', $d);
        $this->quiz($L, 'Từ nào có nghĩa là "con trai"?', ['daughter', 'son', 'father', 'brother'], 1, '"Son" = con trai; "daughter" = con gái.', $d);
        $this->quiz($L, '"Aunt" nghĩa là gì?', ['bác trai', 'cô/dì', 'anh họ', 'em họ'], 1, '"Aunt" = cô/dì/bác gái.', $d);
        $this->quiz($L, 'Trong câu "My uncle is tall.", từ "uncle" nghĩa là gì?', ['bố', 'chú/cậu', 'ông', 'anh trai'], 1, '"Uncle" = chú/cậu/bác trai.', $d);
        $this->matching($L, 'Nối mỗi từ chỉ người thân với nghĩa của nó (nhóm 1).', [['father', 'bố'], ['mother', 'mẹ'], ['brother', 'anh/em trai'], ['sister', 'chị/em gái']], 'father = bố, mother = mẹ, brother = anh/em trai, sister = chị/em gái.', $d);
        $this->matching($L, 'Nối mỗi từ chỉ người thân với nghĩa của nó (nhóm 2).', [['grandfather', 'ông'], ['grandmother', 'bà'], ['uncle', 'chú/cậu'], ['aunt', 'cô/dì']], 'grandfather = ông, grandmother = bà, uncle = chú/cậu, aunt = cô/dì.', $d);
        $this->matching($L, 'Nối mỗi từ chỉ người thân với nghĩa của nó (nhóm 3).', [['son', 'con trai'], ['daughter', 'con gái'], ['cousin', 'anh/chị/em họ'], ['family', 'gia đình']], 'son = con trai, daughter = con gái, cousin = anh/chị/em họ.', $d);
        $this->matching($L, 'Nối mỗi câu tiếng Anh với nghĩa tiếng Việt (gia đình).', [['My father is a doctor.', 'Bố tôi là bác sĩ.'], ['She is my sister.', 'Cô ấy là chị gái tôi.'], ['We love our grandparents.', 'Chúng tôi yêu quý ông bà.'], ['He has one brother.', 'Anh ấy có một em trai.']], 'Đọc cả câu để nối đúng nghĩa.', $d);
        $this->matching($L, 'Nối mỗi từ chỉ người thân với nghĩa của nó (nhóm 4).', [['parents', 'bố mẹ'], ['children', 'con cái'], ['baby', 'em bé'], ['twin', 'sinh đôi']], 'parents = bố mẹ, children = con cái, baby = em bé.', $d);
        $this->sortQ($L, 'Kéo mỗi từ vào nhóm THẾ HỆ TRẺ hoặc THẾ HỆ GIÀ.', [['son', 'THẾ HỆ TRẺ'], ['daughter', 'THẾ HỆ TRẺ'], ['grandfather', 'THẾ HỆ GIÀ'], ['grandmother', 'THẾ HỆ GIÀ']], 'Con cháu là thế hệ trẻ; ông bà là thế hệ già.', $d);
        $this->sortQ($L, 'Kéo mỗi từ vào nhóm CON TRAI hoặc CON GÁI.', [['son', 'CON TRAI'], ['brother', 'CON TRAI'], ['daughter', 'CON GÁI'], ['sister', 'CON GÁI']], 'Son/brother là nam; daughter/sister là nữ.', $d);
        $this->sortQ($L, 'Kéo mỗi từ vào nhóm BỐ MẸ hoặc CON CÁI.', [['father', 'BỐ MẸ'], ['mother', 'BỐ MẸ'], ['son', 'CON CÁI'], ['daughter', 'CON CÁI']], 'Father/mother là bố mẹ; son/daughter là con cái.', $d);
        $this->sortQ($L, 'Kéo mỗi từ vào nhóm SỐ ÍT hoặc SỐ NHIỀU.', [['child', 'SỐ ÍT'], ['man', 'SỐ ÍT'], ['children', 'SỐ NHIỀU'], ['women', 'SỐ NHIỀU']], 'child→children, man→men, woman→women là danh từ bất quy tắc.', $d);
        $this->sortQ($L, 'Kéo mỗi câu vào nhóm ĐÚNG hoặc SAI.', [['My mother is my father\'s wife.', 'ĐÚNG'], ['My sister is my parents\' daughter.', 'ĐÚNG'], ['My uncle is my mother\'s father.', 'SAI'], ['My aunt is my father\'s sister.', 'ĐÚNG']], 'Uncle không phải là ông ngoại; grandfather mới là ông.', $d);
        $this->fill($L, '"Mẹ" trong tiếng Anh là ___.', [[0, 'mother']], '"Mẹ" = mother.', $d);
        $this->fill($L, 'He is my ___. (Anh ấy là em trai tôi.)', [[0, 'brother']], '"Em trai" = brother.', $d);
        $this->fill($L, '"Bà" trong tiếng Anh là ___.', [[0, 'grandmother']], '"Bà" = grandmother.', $d);
        $this->fill($L, 'They are my ___. (Họ là bố mẹ tôi.)', [[0, 'parents']], '"Bố mẹ" = parents.', $d);
        $this->fill($L, 'My ___ is very kind. ("Dì" của tôi rất tốt bụng.)', [[0, 'aunt']], '"Dì" = aunt.', $d);
    }

    // 8. en-tu-vung-lop-6-lop-6-2 (g6, de) - Từ vựng trường học
    private function seedEnSchoolVocab(): void
    {
        $L = 'en-tu-vung-lop-6-lop-6-2'; $d = 'de';
        $this->quiz($L, '"Student" nghĩa là gì?', ['học sinh', 'giáo viên', 'lớp học', 'bài học'], 0, '"Student" = học sinh.', $d);
        $this->quiz($L, '"Classroom" nghĩa là gì?', ['sân trường', 'lớp học', 'thư viện', 'phòng thí nghiệm'], 1, '"Classroom" = lớp học.', $d);
        $this->quiz($L, 'Từ nào có nghĩa là "bút chì"?', ['pen', 'pencil', 'ruler', 'eraser'], 1, '"Pencil" = bút chì; "pen" = bút mực.', $d);
        $this->quiz($L, '"Library" nghĩa là gì?', ['căng tin', 'phòng y tế', 'thư viện', 'phòng học'], 2, '"Library" = thư viện.', $d);
        $this->quiz($L, 'Trong câu "Open your notebook.", "notebook" nghĩa là gì?', ['sách giáo khoa', 'quyển vở', 'cặp sách', 'bút mực'], 1, '"Notebook" = quyển vở.', $d);
        $this->matching($L, 'Nối mỗi đồ dùng học tập với nghĩa của nó (nhóm 1).', [['pen', 'bút mực'], ['pencil', 'bút chì'], ['ruler', 'thước kẻ'], ['eraser', 'cục tẩy']], 'pen = bút mực, pencil = bút chì, ruler = thước kẻ, eraser = cục tẩy.', $d);
        $this->matching($L, 'Nối mỗi đồ dùng học tập với nghĩa của nó (nhóm 2).', [['book', 'sách'], ['notebook', 'quyển vở'], ['bag', 'cặp sách'], ['desk', 'bàn học']], 'book = sách, notebook = quyển vở, bag = cặp sách, desk = bàn học.', $d);
        $this->matching($L, 'Nối mỗi nơi trong trường với nghĩa của nó.', [['classroom', 'lớp học'], ['library', 'thư viện'], ['schoolyard', 'sân trường'], ['canteen', 'căng tin']], 'classroom = lớp học, library = thư viện, schoolyard = sân trường, canteen = căng tin.', $d);
        $this->matching($L, 'Nối mỗi môn học với nghĩa của nó (nhóm 2).', [['Maths', 'Toán'], ['Literature', 'Ngữ văn'], ['History', 'Lịch sử'], ['PE', 'Thể dục']], 'Maths = Toán, Literature = Ngữ văn, History = Lịch sử, PE = Thể dục.', $d);
        $this->matching($L, 'Nối mỗi câu tiếng Anh với nghĩa tiếng Việt (trường học).', [['I like English.', 'Tôi thích môn Tiếng Anh.'], ['She is a good student.', 'Cô ấy là học sinh giỏi.'], ['We learn Maths today.', 'Hôm nay chúng tôi học Toán.'], ['The library is big.', 'Thư viện rất rộng.']], 'Đọc cả câu để nối đúng nghĩa.', $d);
        $this->sortQ($L, 'Kéo mỗi từ vào nhóm ĐỌC hoặc VIẾT.', [['book', 'ĐỌC'], ['newspaper', 'ĐỌC'], ['pen', 'VIẾT'], ['pencil', 'VIẾT']], 'Đọc sách/báo; viết bằng bút.', $d);
        $this->sortQ($L, 'Kéo mỗi từ vào nhóm MÔN XÃ HỘI hoặc MÔN TỰ NHIÊN.', [['Literature', 'MÔN XÃ HỘI'], ['History', 'MÔN XÃ HỘI'], ['Maths', 'MÔN TỰ NHIÊN'], ['Science', 'MÔN TỰ NHIÊN']], 'Văn/Sử là môn xã hội; Toán/Khoa học là môn tự nhiên.', $d);
        $this->sortQ($L, 'Kéo mỗi từ vào nhóm TRONG CẶP SÁCH hoặc NGOÀI CẶP SÁCH.', [['pen', 'TRONG CẶP SÁCH'], ['book', 'TRONG CẶP SÁCH'], ['desk', 'NGOÀI CẶP SÁCH'], ['chair', 'NGOÀI CẶP SÁCH']], 'Bút và sách để trong cặp; bàn ghế thì không.', $d);
        $this->sortQ($L, 'Kéo mỗi câu vào nhóm ĐÚNG hoặc SAI (trường học).', [['A teacher teaches students.', 'ĐÚNG'], ['A library has many books.', 'ĐÚNG'], ['We write with a ruler.', 'SAI'], ['Students sleep in class.', 'SAI']], 'Giáo viên dạy học sinh; thư viện có nhiều sách.', $d);
        $this->sortQ($L, 'Kéo mỗi từ vào nhóm BẮT ĐẦU BẰNG NGUYÊN ÂM hoặc PHỤ ÂM.', [['eraser', 'NGUYÊN ÂM'], ['English', 'NGUYÊN ÂM'], ['pen', 'PHỤ ÂM'], ['book', 'PHỤ ÂM']], 'Nguyên âm: a, e, i, o, u.', $d);
        $this->fill($L, '"Học sinh" trong tiếng Anh là ___.', [[0, 'student']], '"Học sinh" = student.', $d);
        $this->fill($L, 'I learn English in the ___. (Tôi học tiếng Anh trong lớp học.)', [[0, 'classroom']], '"Lớp học" = classroom.', $d);
        $this->fill($L, '"Cặp sách" trong tiếng Anh là ___.', [[0, 'schoolbag']], '"Cặp sách" = schoolbag (hoặc bag).', $d);
        $this->fill($L, 'Our ___ is very kind. (Giáo viên của chúng tôi rất tốt bụng.)', [[0, 'teacher']], '"Giáo viên" = teacher.', $d);
        $this->fill($L, '"Môn Toán" trong tiếng Anh là ___.', [[0, 'Maths']], '"Môn Toán" = Maths.', $d);
    }

    // 9. en-tu-vung-lop-6-lop-7-1 (g7, de) - Hoạt động hằng ngày
    private function seedEnDailyLife(): void
    {
        $L = 'en-tu-vung-lop-6-lop-7-1'; $d = 'de';
        $this->quiz($L, '"Wake up" nghĩa là gì?', ['thức dậy', 'đi ngủ', 'ăn sáng', 'đánh răng'], 0, '"Wake up" = thức dậy.', $d);
        $this->quiz($L, '"Do homework" nghĩa là gì?', ['chơi game', 'làm bài tập về nhà', 'xem TV', 'đọc sách'], 1, '"Do homework" = làm bài tập về nhà.', $d);
        $this->quiz($L, 'Cụm từ nào có nghĩa là "đi học"?', ['go to bed', 'go to school', 'go home', 'get up'], 1, '"Go to school" = đi học.', $d);
        $this->quiz($L, '"Have lunch" nghĩa là gì?', ['ăn sáng', 'ăn trưa', 'ăn tối', 'uống nước'], 1, '"Have lunch" = ăn trưa.', $d);
        $this->quiz($L, '"Watch TV" nghĩa là gì?', ['xem TV', 'nghe nhạc', 'chơi thể thao', 'đọc báo'], 0, '"Watch TV" = xem TV.', $d);
        $this->matching($L, 'Nối mỗi cụm từ buổi sáng với nghĩa của nó.', [['get up', 'thức dậy'], ['brush teeth', 'đánh răng'], ['wash face', 'rửa mặt'], ['have breakfast', 'ăn sáng']], 'get up = thức dậy, brush teeth = đánh răng, wash face = rửa mặt, have breakfast = ăn sáng.', $d);
        $this->matching($L, 'Nối mỗi cụm từ trong ngày với nghĩa của nó.', [['go to school', 'đi học'], ['have lunch', 'ăn trưa'], ['do homework', 'làm bài tập'], ['go to bed', 'đi ngủ']], 'go to school = đi học, have lunch = ăn trưa, do homework = làm bài tập, go to bed = đi ngủ.', $d);
        $this->matching($L, 'Nối mỗi cụm thời gian với nghĩa của nó.', [['in the morning', 'vào buổi sáng'], ['in the afternoon', 'vào buổi chiều'], ['in the evening', 'vào buổi tối'], ['at night', 'vào ban đêm']], 'in the morning = buổi sáng, in the afternoon = buổi chiều, in the evening = buổi tối, at night = ban đêm.', $d);
        $this->matching($L, 'Nối mỗi hoạt động giải trí với nghĩa của nó.', [['play football', 'đá bóng'], ['read books', 'đọc sách'], ['watch TV', 'xem TV'], ['listen to music', 'nghe nhạc']], 'play football = đá bóng, read books = đọc sách, watch TV = xem TV, listen to music = nghe nhạc.', $d);
        $this->matching($L, 'Nối mỗi câu với nghĩa của nó (sinh hoạt hằng ngày).', [['I get up at 6.', 'Tôi thức dậy lúc 6 giờ.'], ['She does homework in the evening.', 'Cô ấy làm bài tập vào buổi tối.'], ['We have lunch at school.', 'Chúng tôi ăn trưa ở trường.'], ['He goes to bed at 10.', 'Anh ấy đi ngủ lúc 10 giờ.']], 'Đọc cả câu để nối đúng nghĩa.', $d);
        $this->sortQ($L, 'Kéo mỗi hoạt động vào nhóm TRONG TUẦN hoặc CUỐI TUẦN.', [['go to school', 'TRONG TUẦN'], ['do homework', 'TRONG TUẦN'], ['visit grandparents', 'CUỐI TUẦN'], ['go swimming', 'CUỐI TUẦN']], 'Đi học/làm bài tập trong tuần; thăm ông bà/đi bơi cuối tuần.', $d);
        $this->sortQ($L, 'Kéo mỗi từ vào nhóm ĐỘNG TỪ HÀNH ĐỘNG hoặc CỤM THỜI GIAN.', [['get up', 'ĐỘNG TỪ HÀNH ĐỘNG'], ['eat', 'ĐỘNG TỪ HÀNH ĐỘNG'], ['in the morning', 'CỤM THỜI GIAN'], ['at night', 'CỤM THỜI GIAN']], 'Get up/eat là hành động; in the morning/at night chỉ thời gian.', $d);
        $this->sortQ($L, 'Kéo mỗi hoạt động vào nhóm CẦN NƯỚC hoặc KHÔNG CẦN NƯỚC.', [['brush teeth', 'CẦN NƯỚC'], ['wash face', 'CẦN NƯỚC'], ['take a shower', 'CẦN NƯỚC'], ['do homework', 'KHÔNG CẦN NƯỚC']], 'Đánh răng, rửa mặt, tắm cần nước.', $d);
        $this->sortQ($L, 'Kéo mỗi câu vào nhóm ĐÚNG hoặc SAI (sinh hoạt).', [['We have breakfast in the morning.', 'ĐÚNG'], ['She brushes her teeth every day.', 'ĐÚNG'], ['Students go to bed at school.', 'SAI'], ['He eats lunch at midnight.', 'SAI']], 'Ăn sáng buổi sáng, đánh răng mỗi ngày là đúng.', $d);
        $this->sortQ($L, 'Kéo mỗi hoạt động vào nhóm BUỔI TRƯA hoặc BUỔI CHIỀU.', [['have lunch', 'BUỔI TRƯA'], ['take a nap', 'BUỔI TRƯA'], ['play sports', 'BUỔI CHIỀU'], ['do homework', 'BUỔI CHIỀU']], 'Ăn trưa/ngủ trưa vào buổi trưa; chơi thể thao/làm bài tập buổi chiều.', $d);
        $this->fill($L, '"Đánh răng" trong tiếng Anh là brush ___.', [[0, 'teeth']], '"Đánh răng" = brush teeth.', $d);
        $this->fill($L, 'I ___ up at 6 a.m. (Tôi thức dậy lúc 6 giờ sáng.)', [[0, 'get']], '"Thức dậy" = get up.', $d);
        $this->fill($L, '"Ăn tối" trong tiếng Anh là have ___.', [[0, 'dinner']], '"Ăn tối" = have dinner.', $d);
        $this->fill($L, 'She ___ her face every morning. (Cô ấy rửa mặt mỗi sáng.)', [[0, 'washes']], '"Rửa mặt" = wash face; she → washes.', $d);
        $this->fill($L, 'We ___ lunch at 11:30. (Chúng tôi ăn trưa lúc 11:30.)', [[0, 'have']], '"Ăn trưa" = have lunch.', $d);
    }

    // 10. en-tu-vung-lop-6-lop-7-2 (g7, trung_binh) - Thời tiết và thiên nhiên
    private function seedEnWeather(): void
    {
        $L = 'en-tu-vung-lop-6-lop-7-2'; $d = 'trung_binh';
        $this->quiz($L, '"Cloudy" nghĩa là gì?', ['nhiều mây', 'nắng', 'mưa', 'gió'], 0, '"Cloudy" = nhiều mây.', $d);
        $this->quiz($L, '"Snowy" nghĩa là gì?', ['mưa', 'có tuyết', 'nắng', 'sương mù'], 1, '"Snowy" = có tuyết.', $d);
        $this->quiz($L, 'Từ nào có nghĩa là "sấm"?', ['wind', 'storm', 'thunder', 'fog'], 2, '"Thunder" = sấm; "lightning" = chớp.', $d);
        $this->quiz($L, '"Storm" nghĩa là gì?', ['mưa nhỏ', 'gió nhẹ', 'nắng đẹp', 'bão'], 3, '"Storm" = bão.', $d);
        $this->quiz($L, 'Câu "It\'s freezing today." nghĩa là gì?', ['Hôm nay trời lạnh buốt.', 'Hôm nay trời nóng.', 'Hôm nay trời đẹp.', 'Hôm nay trời mưa.'], 0, '"Freezing" = lạnh buốt.', $d);
        $this->matching($L, 'Nối mỗi tính từ thời tiết với nghĩa của nó (nhóm 1).', [['sunny', 'nắng'], ['rainy', 'mưa'], ['windy', 'gió'], ['cloudy', 'nhiều mây']], 'sunny = nắng, rainy = mưa, windy = gió, cloudy = nhiều mây.', $d);
        $this->matching($L, 'Nối mỗi hiện tượng thời tiết với nghĩa của nó.', [['storm', 'bão'], ['snow', 'tuyết'], ['fog', 'sương mù'], ['rainbow', 'cầu vồng']], 'storm = bão, snow = tuyết, fog = sương mù, rainbow = cầu vồng.', $d);
        $this->matching($L, 'Nối mỗi từ chỉ cảnh quan với nghĩa của nó.', [['mountain', 'núi'], ['river', 'sông'], ['beach', 'bãi biển'], ['forest', 'rừng']], 'mountain = núi, river = sông, beach = bãi biển, forest = rừng.', $d);
        $this->matching($L, 'Nối mỗi mùa với tên tiếng Việt của nó.', [['spring', 'mùa xuân'], ['summer', 'mùa hè'], ['autumn', 'mùa thu'], ['winter', 'mùa đông']], 'spring = xuân, summer = hè, autumn = thu, winter = đông.', $d);
        $this->matching($L, 'Nối mỗi câu với nghĩa của nó (thời tiết).', [['It is raining.', 'Trời đang mưa.'], ['The wind is strong.', 'Gió rất mạnh.'], ['It is hot today.', 'Hôm nay trời nóng.'], ['I like winter.', 'Tôi thích mùa đông.']], 'Đọc cả câu để nối đúng nghĩa.', $d);
        $this->sortQ($L, 'Kéo mỗi từ vào nhóm CÓ MƯA hoặc KHÔNG MƯA.', [['rainy', 'CÓ MƯA'], ['storm', 'CÓ MƯA'], ['sunny', 'KHÔNG MƯA'], ['cloudy', 'KHÔNG MƯA']], 'Rainy/storm có mưa; sunny/cloudy không mưa.', $d);
        $this->sortQ($L, 'Kéo mỗi từ vào nhóm GỢI MÙA HÈ hoặc GỢI MÙA ĐÔNG.', [['hot', 'GỢI MÙA HÈ'], ['sun', 'GỢI MÙA HÈ'], ['snow', 'GỢI MÙA ĐÔNG'], ['cold', 'GỢI MÙA ĐÔNG']], 'Mùa hè nóng, có nắng; mùa đông lạnh, có tuyết.', $d);
        $this->sortQ($L, 'Kéo mỗi từ vào nhóm TRÊN TRỜI hoặc DƯỚI ĐẤT.', [['sun', 'TRÊN TRỜI'], ['cloud', 'TRÊN TRỜI'], ['rainbow', 'TRÊN TRỜI'], ['river', 'DƯỚI ĐẤT'], ['mountain', 'DƯỚI ĐẤT']], 'Mặt trời, mây, cầu vồng ở trên trời; sông, núi ở dưới đất.', $d);
        $this->sortQ($L, 'Kéo mỗi câu vào nhóm ĐÚNG hoặc SAI (thời tiết).', [['It snows in winter in Sapa.', 'ĐÚNG'], ['Rainbows appear after rain.', 'ĐÚNG'], ['It is always cold in summer.', 'SAI'], ['The sun rises in the west.', 'SAI']], 'Sa Pa có tuyết mùa đông; cầu vồng xuất hiện sau mưa.', $d);
        $this->sortQ($L, 'Kéo mỗi từ vào nhóm THỜI TIẾT THƯỜNG GẶP hoặc HIẾM GẶP ở Việt Nam.', [['rain', 'THƯỜNG GẶP'], ['hot', 'THƯỜNG GẶP'], ['snow', 'HIẾM GẶP'], ['fog', 'HIẾM GẶP']], 'Việt Nam thường mưa và nóng; tuyết hiếm gặp.', $d);
        $this->fill($L, '"Gió" (danh từ) trong tiếng Anh là ___.', [[0, 'wind']], '"Gió" = wind; "windy" = có gió.', $d);
        $this->fill($L, 'It is ___ today. It is very cold! (Hôm nay tuyết rơi. Trời rất lạnh!)', [[0, 'snowing']], '"Tuyết rơi" = snowing.', $d);
        $this->fill($L, '"Cầu vồng" trong tiếng Anh là ___.', [[0, 'rainbow']], '"Cầu vồng" = rainbow.', $d);
        $this->fill($L, 'In ___, leaves turn yellow and fall. (Vào mùa thu, lá vàng và rụng.)', [[0, 'autumn']], '"Mùa thu" = autumn (hoặc fall).', $d);
        $this->fill($L, 'The ___ is shining brightly. (Mặt trời đang chiếu sáng rực rỡ.)', [[0, 'sun']], '"Mặt trời" = sun.', $d);
    }

    // 11. en-ngu-phap-co-ban-lop-6-1 (g6, de) - Động từ to be
    private function seedEnToBe(): void
    {
        $L = 'en-ngu-phap-co-ban-lop-6-1'; $d = 'de';
        $this->quiz($L, 'Điền từ đúng: "He ___ my brother."', ['is', 'am', 'are', 'be'], 0, 'He/she/it đi với "is".', $d);
        $this->quiz($L, 'Điền từ đúng: "We ___ students."', ['is', 'are', 'am', 'be'], 1, 'We/you/they đi với "are".', $d);
        $this->quiz($L, 'Điền từ đúng: "It ___ a cat."', ['are', 'am', 'is', 'be'], 2, 'It đi với "is".', $d);
        $this->quiz($L, 'Chọn câu đúng:', ['She am happy.', 'I is a student.', 'They is tall.', 'We are friends.'], 3, 'Chỉ "We are friends." đúng: we đi với are.', $d);
        $this->quiz($L, 'Điền từ đúng: "You ___ my best friend."', ['are', 'is', 'am', 'be'], 0, 'You (số ít hay số nhiều) đều đi với "are".', $d);
        $this->matching($L, 'Nối mỗi chủ ngữ với dạng to be đúng (phần 2).', [['You', 'are'], ['We', 'are'], ['It', 'is'], ['I', 'am']], 'I→am, he/she/it→is, you/we/they→are.', $d);
        $this->matching($L, 'Nối mỗi câu khẳng định với câu phủ định của nó (phần 2).', [['She is tall.', 'She is not tall.'], ['They are happy.', 'They are not happy.'], ['I am tired.', 'I am not tired.'], ['He is at home.', 'He is not at home.']], 'Câu phủ định: to be + not.', $d);
        $this->matching($L, 'Nối mỗi câu hỏi với câu trả lời đúng (phần 2).', [['Are you OK?', 'Yes, I am.'], ['Is he a doctor?', 'No, he isn\'t.'], ['Are they friends?', 'Yes, they are.'], ['Am I late?', 'No, you aren\'t.']], 'Are you→I am; Is he→he isn\'t.', $d);
        $this->matching($L, 'Nối mỗi dạng đầy đủ với dạng rút gọn.', [['I am', 'I\'m'], ['She is', 'She\'s'], ['They are', 'They\'re'], ['It is not', 'It isn\'t']], 'I\'m = I am, she\'s = she is, they\'re = they are, isn\'t = is not.', $d);
        $this->matching($L, 'Nối mỗi câu với nghĩa của nó (phần 2).', [['I am 12.', 'Tôi 12 tuổi.'], ['She is my teacher.', 'Cô ấy là giáo viên của tôi.'], ['We are happy.', 'Chúng tôi rất vui.'], ['They are students.', 'Họ là học sinh.']], 'Đọc cả câu để nối đúng nghĩa.', $d);
        $this->sortQ($L, 'Kéo mỗi câu vào nhóm DÙNG "AM" hoặc DÙNG "IS/ARE".', [['I am fine.', 'DÙNG "AM"'], ['I am ready.', 'DÙNG "AM"'], ['She is fine.', 'DÙNG "IS/ARE"'], ['They are fine.', 'DÙNG "IS/ARE"']], 'Chỉ "I" đi với "am".', $d);
        $this->sortQ($L, 'Kéo mỗi câu vào nhóm CÓ DẠNG RÚT GỌN hoặc KHÔNG.', [['I\'m happy.', 'CÓ DẠNG RÚT GỌN'], ['She\'s tall.', 'CÓ DẠNG RÚT GỌN'], ['I am happy.', 'KHÔNG'], ['She is tall.', 'KHÔNG']], 'I\'m = I am; she\'s = she is.', $d);
        $this->sortQ($L, 'Kéo mỗi câu vào nhóm ĐÚNG hoặc SAI (to be).', [['He is tall.', 'ĐÚNG'], ['I am 12.', 'ĐÚNG'], ['She are my sister.', 'SAI'], ['We is friends.', 'SAI']], 'She→is, we→are.', $d);
        $this->sortQ($L, 'Kéo mỗi từ vào nhóm CHỦ NGỮ hoặc ĐỘNG TỪ TO BE.', [['I', 'CHỦ NGỮ'], ['they', 'CHỦ NGỮ'], ['am', 'ĐỘNG TỪ TO BE'], ['are', 'ĐỘNG TỪ TO BE']], 'I/they là chủ ngữ; am/are là động từ to be.', $d);
        $this->sortQ($L, 'Kéo mỗi câu vào nhóm CÂU HỎI YES/NO hoặc CÂU HỎI WH-.', [['Are you ready?', 'CÂU HỎI YES/NO'], ['Is she at home?', 'CÂU HỎI YES/NO'], ['Where are you?', 'CÂU HỎI WH-'], ['What is this?', 'CÂU HỎI WH-']], 'Yes/No question đảo to be lên đầu; Wh- question có từ để hỏi.', $d);
        $this->fill($L, 'He ___ my best friend. (Anh ấy là bạn thân của tôi.)', [[0, 'is']], 'He → is.', $d);
        $this->fill($L, 'They ___ not at school today. (Hôm nay họ không ở trường.)', [[0, 'are']], 'They → are.', $d);
        $this->fill($L, '___ I late? (Tôi có đến muộn không?)', [[0, 'Am']], 'Câu hỏi với I: Am I...?', $d);
        $this->fill($L, 'It ___ a beautiful day! (Hôm nay trời đẹp!)', [[0, 'is']], 'It → is.', $d);
        $this->fill($L, 'You ___ very kind. (Bạn rất tốt bụng.)', [[0, 'are']], 'You → are.', $d);
    }

    // 12. en-ngu-phap-co-ban-lop-6-2 (g6, de) - Thì hiện tại đơn
    private function seedEnPresentSimple2(): void
    {
        $L = 'en-ngu-phap-co-ban-lop-6-2'; $d = 'de';
        $this->quiz($L, 'Điền từ đúng: "My father ___ to work by bus."', ['go', 'goes', 'going', 'is go'], 1, 'My father = he → goes.', $d);
        $this->quiz($L, 'Điền từ đúng: "Dogs ___ bones."', ['like', 'likes', 'liking', 'is like'], 0, 'Dogs (số nhiều) → like.', $d);
        $this->quiz($L, 'Điền từ đúng: "The sun ___ in the east."', ['rise', 'rises', 'rising', 'is rise'], 1, 'The sun = it → rises.', $d);
        $this->quiz($L, 'Câu nào đúng ngữ pháp?', ['She go to school.', 'They goes home.', 'He play chess.', 'We like music.'], 3, 'Chỉ "We like music." đúng: we + động từ nguyên thể.', $d);
        $this->quiz($L, 'Điền từ đúng: "I ___ my teeth every morning."', ['brush', 'brushes', 'brushing', 'am brush'], 0, 'I → brush.', $d);
        $this->matching($L, 'Nối mỗi chủ ngữ với dạng "go" đúng (phần 2).', [['He', 'goes'], ['We', 'go'], ['She', 'goes'], ['I', 'go']], 'He/she → goes; I/we/they → go.', $d);
        $this->matching($L, 'Nối mỗi động từ với dạng thêm s/es (phần 2).', [['do', 'does'], ['study', 'studies'], ['fix', 'fixes'], ['cry', 'cries']], 'do→does, study→studies, fix→fixes, cry→cries.', $d);
        $this->matching($L, 'Nối mỗi câu với nghĩa của nó (phần 2).', [['She likes apples.', 'Cô ấy thích táo.'], ['They play chess.', 'Họ chơi cờ.'], ['He watches TV.', 'Anh ấy xem TV.'], ['We go home.', 'Chúng tôi về nhà.']], 'Đọc cả câu để nối đúng nghĩa.', $d);
        $this->matching($L, 'Nối mỗi dấu hiệu với thì nó báo hiệu.', [['every day', 'hiện tại đơn'], ['now', 'hiện tại tiếp diễn'], ['yesterday', 'quá khứ đơn'], ['tomorrow', 'tương lai đơn']], 'every day→hiện tại đơn; now→tiếp diễn; yesterday→quá khứ; tomorrow→tương lai.', $d);
        $this->matching($L, 'Nối mỗi câu hỏi với câu trả lời đúng (hiện tại đơn).', [['Does she like tea?', 'Yes, she does.'], ['Do you swim?', 'Yes, I do.'], ['Does he run?', 'No, he doesn\'t.'], ['Do they dance?', 'No, they don\'t.']], 'Does→she/he; do→you/they.', $d);
        $this->sortQ($L, 'Kéo mỗi câu vào nhóm HIỆN TẠI ĐƠN hoặc QUÁ KHỨ ĐƠN.', [['She plays tennis.', 'HIỆN TẠI ĐƠN'], ['He works hard.', 'HIỆN TẠI ĐƠN'], ['She played tennis.', 'QUÁ KHỨ ĐƠN'], ['He worked hard.', 'QUÁ KHỨ ĐƠN']], 'Hiện tại đơn: play/plays; quá khứ đơn: played.', $d);
        $this->sortQ($L, 'Kéo mỗi động từ vào nhóm THÊM -ES hoặc THÊM -S THƯỜNG.', [['goes', 'THÊM -ES'], ['watches', 'THÊM -ES'], ['plays', 'THÊM -S THƯỜNG'], ['runs', 'THÊM -S THƯỜNG']], 'Tận cùng -o, -ch thêm -es; còn lại thêm -s.', $d);
        $this->sortQ($L, 'Kéo mỗi câu vào nhóm THÓI QUEN hoặc SỰ THẬT HIỂN NHIÊN.', [['I drink milk daily.', 'THÓI QUEN'], ['She walks to school.', 'THÓI QUEN'], ['Fish live in water.', 'SỰ THẬT HIỂN NHIÊN'], ['The earth moves.', 'SỰ THẬT HIỂN NHIÊN']], 'Hiện tại đơn diễn tả thói quen và sự thật hiển nhiên.', $d);
        $this->sortQ($L, 'Kéo mỗi từ vào nhóm TRẠNG TỪ CHỈ TẦN SUẤT hoặc KHÁC.', [['always', 'TRẠNG TỪ CHỈ TẦN SUẤT'], ['never', 'TRẠNG TỪ CHỈ TẦN SUẤT'], ['yesterday', 'KHÁC'], ['tomorrow', 'KHÁC']], 'always/never chỉ tần suất; yesterday/tomorrow chỉ thời điểm.', $d);
        $this->sortQ($L, 'Kéo mỗi câu vào nhóm ĐÚNG hoặc SAI (phần 2).', [['She doesn\'t like fish.', 'ĐÚNG'], ['We play well.', 'ĐÚNG'], ['He don\'t swim.', 'SAI'], ['They goes out.', 'SAI']], 'He→doesn\'t; they→go.', $d);
        $this->fill($L, 'I ___ (get) up at 6 every day.', [[0, 'get']], 'I → get.', $d);
        $this->fill($L, 'The train ___ (leave) at 7 a.m.', [[0, 'leaves']], 'The train = it → leaves.', $d);
        $this->fill($L, 'She ___ (not/eat) meat.', [[0, 'does not eat']], 'Phủ định với she: does not + động từ nguyên thể.', $d);
        $this->fill($L, '___ he ___ (play) football? (Anh ấy có chơi bóng đá không?)', [[0, 'Does'], [1, 'play']], 'Câu hỏi với he: Does + he + play?', $d);
        $this->fill($L, 'Birds ___ (fly) in the sky.', [[0, 'fly']], 'Birds (số nhiều) → fly.', $d);
    }

    // 13. en-ngu-phap-co-ban-lop-7-1 (g7, trung_binh) - Phủ định, nghi vấn hiện tại đơn
    private function seedEnNegQuestion(): void
    {
        $L = 'en-ngu-phap-co-ban-lop-7-1'; $d = 'trung_binh';
        $this->quiz($L, 'Điền từ đúng: "I ___ like coffee."', ['don\'t', 'doesn\'t', 'isn\'t', 'not'], 0, 'I → don\'t.', $d);
        $this->quiz($L, 'Điền từ đúng: "___ he play chess?"', ['Is', 'Does', 'Do', 'Has'], 1, 'Câu hỏi với he: Does he...?', $d);
        $this->quiz($L, 'Điền từ đúng: "They do not ___ meat."', ['eats', 'ate', 'eat', 'eating'], 2, 'Sau do/do not là động từ nguyên thể: eat.', $d);
        $this->quiz($L, 'Chọn câu đúng:', ['She don\'t swim.', 'He doesn\'t likes tea.', 'Do she work here?', 'Does he play well?'], 3, 'Chỉ "Does he play well?" đúng.', $d);
        $this->quiz($L, 'Điền từ đúng: "___ your parents work here?"', ['Do', 'Does', 'Is', 'Are'], 0, 'Your parents (số nhiều) → Do.', $d);
        $this->matching($L, 'Nối mỗi chủ ngữ với trợ động từ đúng trong câu hỏi (phần 2).', [['She', 'does'], ['They', 'do'], ['He', 'does'], ['We', 'do']], 'She/he → does; they/we → do.', $d);
        $this->matching($L, 'Nối mỗi câu khẳng định với câu phủ định của nó (phần 2).', [['She likes tea.', 'She doesn\'t like tea.'], ['They swim well.', 'They don\'t swim well.'], ['He works here.', 'He doesn\'t work here.'], ['We eat rice.', 'We don\'t eat rice.']], 'Phủ định: don\'t/doesn\'t + động từ nguyên thể.', $d);
        $this->matching($L, 'Nối mỗi câu hỏi với câu trả lời ngắn (phần 2).', [['Do you like it?', 'Yes, I do.'], ['Does she sing?', 'No, she doesn\'t.'], ['Do they run?', 'Yes, they do.'], ['Does he cook?', 'No, he doesn\'t.']], 'Do→you/they; does→she/he.', $d);
        $this->matching($L, 'Nối mỗi từ để hỏi với nghĩa của nó.', [['What', 'cái gì'], ['Where', 'ở đâu'], ['When', 'khi nào'], ['Why', 'tại sao']], 'What = cái gì, where = ở đâu, when = khi nào, why = tại sao.', $d);
        $this->matching($L, 'Nối mỗi lỗi sai với cách sửa đúng.', [['She don\'t go.', 'She doesn\'t go.'], ['Does they swim?', 'Do they swim?'], ['He doesn\'t likes.', 'He doesn\'t like.'], ['Does she works?', 'Does she work?']], 'Sau don\'t/doesn\'t/does là động từ nguyên thể.', $d);
        $this->sortQ($L, 'Kéo mỗi câu vào nhóm CÂU HỎI YES/NO hoặc CÂU HỎI WH- (hiện tại đơn).', [['Do you swim?', 'CÂU HỎI YES/NO'], ['Does he run?', 'CÂU HỎI YES/NO'], ['Where do you live?', 'CÂU HỎI WH-'], ['Why does she cry?', 'CÂU HỎI WH-']], 'Wh- question có từ để hỏi ở đầu.', $d);
        $this->sortQ($L, 'Kéo mỗi câu trả lời ngắn vào nhóm ĐÚNG hoặc SAI.', [['Yes, she does.', 'ĐÚNG'], ['No, they don\'t.', 'ĐÚNG'], ['Yes, she do.', 'SAI'], ['No, he doesn\'ts.', 'SAI']], 'Câu trả lời ngắn: Yes/No + S + do/does.', $d);
        $this->sortQ($L, 'Kéo mỗi câu vào nhóm ĐÚNG hoặc SAI (phần 3).', [['She doesn\'t eat meat.', 'ĐÚNG'], ['They don\'t go out.', 'ĐÚNG'], ['Do he like it?', 'SAI'], ['Does they play?', 'SAI']], 'He→does; they→do.', $d);
        $this->sortQ($L, 'Kéo mỗi trợ động từ vào nhóm ĐI VỚI HE/SHE hoặc ĐI VỚI I/YOU/WE/THEY.', [['does', 'ĐI VỚI HE/SHE'], ['doesn\'t', 'ĐI VỚI HE/SHE'], ['do', 'ĐI VỚI I/YOU/WE/THEY'], ['don\'t', 'ĐI VỚI I/YOU/WE/THEY']], 'does/doesn\'t đi với ngôi thứ ba số ít.', $d);
        $this->sortQ($L, 'Kéo mỗi câu vào nhóm CÂU PHỦ ĐỊNH hoặc CÂU NGHI VẤN (phần 2).', [['He doesn\'t swim.', 'CÂU PHỦ ĐỊNH'], ['They don\'t eat.', 'CÂU PHỦ ĐỊNH'], ['Does he swim?', 'CÂU NGHI VẤN'], ['Do they eat?', 'CÂU NGHI VẤN']], 'Phủ định có don\'t/doesn\'t; nghi vấn đảo do/does lên đầu.', $d);
        $this->fill($L, 'She ___ (not/drink) milk.', [[0, 'does not drink']], 'She → does not + drink.', $d);
        $this->fill($L, '___ they ___ (live) here? (Họ có sống ở đây không?)', [[0, 'Do'], [1, 'live']], 'They → Do they live...?', $d);
        $this->fill($L, 'I do not ___ (watch) TV at night.', [[0, 'watch']], 'Sau do not là động từ nguyên thể.', $d);
        $this->fill($L, 'Why ___ she ___ (cry)? (Tại sao cô ấy khóc?)', [[0, 'does'], [1, 'cry']], 'Why does she cry?', $d);
        $this->fill($L, 'We ___ (not/play) games in class.', [[0, 'do not play']], 'We → do not + play.', $d);
    }

    // 14. en-ngu-phap-co-ban-lop-7-2 (g7, trung_binh) - Hiện tại tiếp diễn
    private function seedEnPresentContinuous(): void
    {
        $L = 'en-ngu-phap-co-ban-lop-7-2'; $d = 'trung_binh';
        $this->quiz($L, 'Điền từ đúng: "Look! He ___ running."', ['is', 'are', 'am', 'be'], 0, 'He → is + V-ing.', $d);
        $this->quiz($L, 'Điền từ đúng: "They ___ watching TV now."', ['is', 'are', 'am', 'be'], 1, 'They → are + V-ing.', $d);
        $this->quiz($L, 'Điền cụm từ đúng: "She ___ to music at the moment."', ['is listen', 'is listening', 'are listening', 'listens'], 1, 'She → is listening.', $d);
        $this->quiz($L, 'Chọn câu đúng:', ['She is swim now.', 'They are play chess.', 'I am reading a book.', 'He are running.'], 2, 'Chỉ "I am reading a book." đúng cấu trúc.', $d);
        $this->quiz($L, 'Điền từ đúng: "___ you doing now?"', ['Is', 'Am', 'Are', 'What'], 3, 'Hỏi "bạn đang làm gì": What are you doing?', $d);
        $this->matching($L, 'Nối mỗi chủ ngữ với cấu trúc tiếp diễn đúng (phần 2).', [['He', 'is + V-ing'], ['They', 'are + V-ing'], ['I', 'am + V-ing'], ['She', 'is + V-ing']], 'I→am, he/she/it→is, you/we/they→are + V-ing.', $d);
        $this->matching($L, 'Nối mỗi động từ với dạng V-ing đúng (phần 2).', [['run', 'running'], ['swim', 'swimming'], ['write', 'writing'], ['dance', 'dancing']], 'run→running, swim→swimming, write→writing, dance→dancing.', $d);
        $this->matching($L, 'Nối mỗi câu với nghĩa của nó (phần 2).', [['She is cooking.', 'Cô ấy đang nấu ăn.'], ['They are playing.', 'Họ đang chơi.'], ['I am studying.', 'Tôi đang học bài.'], ['He is sleeping.', 'Anh ấy đang ngủ.']], 'Be + V-ing diễn tả hành động đang xảy ra.', $d);
        $this->matching($L, 'Nối mỗi dấu hiệu với thì của nó (phần 2).', [['at the moment', 'hiện tại tiếp diễn'], ['every day', 'hiện tại đơn'], ['Look!', 'hiện tại tiếp diễn'], ['usually', 'hiện tại đơn']], 'at the moment/Look! → tiếp diễn; every day/usually → hiện tại đơn.', $d);
        $this->matching($L, 'Nối mỗi câu khẳng định tiếp diễn với câu phủ định của nó.', [['She is singing.', 'She is not singing.'], ['They are running.', 'They are not running.'], ['I am eating.', 'I am not eating.'], ['He is working.', 'He is not working.']], 'Phủ định: be + not + V-ing.', $d);
        $this->sortQ($L, 'Kéo mỗi câu vào nhóm ĐANG DIỄN RA hoặc THÓI QUEN.', [['She is cooking now.', 'ĐANG DIỄN RA'], ['They are playing at the moment.', 'ĐANG DIỄN RA'], ['She cooks daily.', 'THÓI QUEN'], ['They play on Sundays.', 'THÓI QUEN']], 'Tiếp diễn = đang diễn ra; hiện tại đơn = thói quen.', $d);
        $this->sortQ($L, 'Kéo mỗi dạng V-ing vào nhóm GẤP ĐÔI PHỤ ÂM hoặc KHÔNG.', [['running', 'GẤP ĐÔI PHỤ ÂM'], ['swimming', 'GẤP ĐÔI PHỤ ÂM'], ['playing', 'KHÔNG'], ['reading', 'KHÔNG']], 'run→running, swim→swimming gấp đôi phụ âm cuối.', $d);
        $this->sortQ($L, 'Kéo mỗi câu vào nhóm ĐÚNG hoặc SAI (phần 3).', [['She is dancing.', 'ĐÚNG'], ['He is eating.', 'ĐÚNG'], ['They are swiming.', 'SAI'], ['I am read now.', 'SAI']], 'swim→swimming (gấp đôi m); read→reading.', $d);
        $this->sortQ($L, 'Kéo mỗi cụm từ vào nhóm DẤU HIỆU TIẾP DIỄN hoặc KHÁC.', [['now', 'DẤU HIỆU TIẾP DIỄN'], ['at the moment', 'DẤU HIỆU TIẾP DIỄN'], ['Look!', 'DẤU HIỆU TIẾP DIỄN'], ['every day', 'KHÁC'], ['yesterday', 'KHÁC']], 'now/at the moment/Look! báo hiệu tiếp diễn.', $d);
        $this->sortQ($L, 'Kéo mỗi chủ ngữ vào nhóm ĐI VỚI AM, IS hoặc ARE.', [['I', 'AM'], ['She', 'IS'], ['They', 'ARE'], ['We', 'ARE']], 'I→am, she→is, they/we→are.', $d);
        $this->fill($L, 'Listen! Someone ___ (knock) at the door.', [[0, 'is knocking']], 'Someone = it → is knocking.', $d);
        $this->fill($L, 'We ___ (have) dinner now.', [[0, 'are having']], 'We → are having.', $d);
        $this->fill($L, 'She ___ (not/watch) TV at the moment.', [[0, 'is not watching']], 'She → is not + watching.', $d);
        $this->fill($L, '___ he ___ (sleep) now? (Anh ấy đang ngủ à?)', [[0, 'Is'], [1, 'sleeping']], 'Is he sleeping now?', $d);
        $this->fill($L, 'Dấu hiệu "at the moment" cho biết thì hiện tại tiếp ___.', [[0, 'diễn']], '"At the moment" = ngay lúc này → tiếp diễn.', $d);
    }

    // 15. en-tu-vung-lop-7-lop-7-1 (g7, trung_binh) - Từ vựng sức khỏe
    private function seedEnHealthVocab2(): void
    {
        $L = 'en-tu-vung-lop-7-lop-7-1'; $d = 'trung_binh';
        $this->quiz($L, '"Cough" nghĩa là gì?', ['ho', 'sốt', 'cảm lạnh', 'hắt hơi'], 0, '"Cough" = ho.', $d);
        $this->quiz($L, '"Toothache" nghĩa là gì?', ['đau bụng', 'đau răng', 'đau đầu', 'đau mắt'], 1, '"Toothache" = đau răng.', $d);
        $this->quiz($L, '"Patient" (bệnh nhân) nghĩa là gì?', ['bác sĩ', 'y tá', 'bệnh nhân', 'dược sĩ'], 2, '"Patient" = bệnh nhân.', $d);
        $this->quiz($L, '"Prescription" nghĩa là gì?', ['thuốc nhỏ mắt', 'khẩu trang', 'nhiệt kế', 'đơn thuốc'], 3, '"Prescription" = đơn thuốc.', $d);
        $this->quiz($L, '"Take a rest" nghĩa là gì?', ['tập thể dục', 'nghỉ ngơi', 'ăn kiêng', 'thức khuya'], 1, '"Take a rest" = nghỉ ngơi.', $d);
        $this->matching($L, 'Nối mỗi triệu chứng với nghĩa của nó (nhóm 1).', [['cough', 'ho'], ['sneeze', 'hắt hơi'], ['sore throat', 'đau họng'], ['runny nose', 'sổ mũi']], 'cough = ho, sneeze = hắt hơi, sore throat = đau họng, runny nose = sổ mũi.', $d);
        $this->matching($L, 'Nối mỗi đồ dùng y tế với nghĩa của nó.', [['pill', 'viên thuốc'], ['syrup', 'siro'], ['bandage', 'băng gạc'], ['thermometer', 'nhiệt kế']], 'pill = viên thuốc, syrup = siro, bandage = băng gạc, thermometer = nhiệt kế.', $d);
        $this->matching($L, 'Nối mỗi bộ phận cơ thể với nghĩa của nó (nhóm 2).', [['eye', 'mắt'], ['ear', 'tai'], ['nose', 'mũi'], ['mouth', 'miệng']], 'eye = mắt, ear = tai, nose = mũi, mouth = miệng.', $d);
        $this->matching($L, 'Nối mỗi từ chỉ nơi khám bệnh với nghĩa của nó.', [['dentist', 'nha sĩ'], ['pharmacy', 'hiệu thuốc'], ['clinic', 'phòng khám'], ['ambulance', 'xe cứu thương']], 'dentist = nha sĩ, pharmacy = hiệu thuốc, clinic = phòng khám, ambulance = xe cứu thương.', $d);
        $this->matching($L, 'Nối mỗi lời khuyên sức khỏe với nghĩa của nó.', [['Drink more water.', 'Uống nhiều nước hơn.'], ['Get enough sleep.', 'Ngủ đủ giấc.'], ['Do exercise daily.', 'Tập thể dục hằng ngày.'], ['Wash your hands.', 'Rửa tay sạch.']], 'Đọc cả câu để nối đúng nghĩa.', $d);
        $this->sortQ($L, 'Kéo mỗi từ vào nhóm BỆNH NHẸ hoặc BỆNH NẶNG.', [['cold', 'BỆNH NHẸ'], ['cough', 'BỆNH NHẸ'], ['flu', 'BỆNH NẶNG'], ['pneumonia', 'BỆNH NẶNG']], 'Cold/cough thường nhẹ; flu/pneumonia nặng hơn.', $d);
        $this->sortQ($L, 'Kéo mỗi từ vào nhóm BÊN TRONG hoặc BÊN NGOÀI CƠ THỂ.', [['heart', 'BÊN TRONG'], ['lungs', 'BÊN TRONG'], ['skin', 'BÊN NGOÀI'], ['hair', 'BÊN NGOÀI']], 'Heart/lungs ở bên trong; skin/hair ở bên ngoài.', $d);
        $this->sortQ($L, 'Kéo mỗi thói quen vào nhóm TỐT CHO RĂNG hoặc XẤU CHO RĂNG.', [['brush teeth', 'TỐT CHO RĂNG'], ['visit the dentist', 'TỐT CHO RĂNG'], ['eat lots of candy', 'XẤU CHO RĂNG'], ['drink soda daily', 'XẤU CHO RĂNG']], 'Đánh răng, khám nha sĩ tốt cho răng; kẹo, soda có hại.', $d);
        $this->sortQ($L, 'Kéo mỗi từ vào nhóm NGƯỜI BỆNH hoặc NGƯỜI CHỮA BỆNH.', [['patient', 'NGƯỜI BỆNH'], ['doctor', 'NGƯỜI CHỮA BỆNH'], ['nurse', 'NGƯỜI CHỮA BỆNH'], ['dentist', 'NGƯỜI CHỮA BỆNH']], 'Patient là người bệnh; doctor/nurse/dentist chữa bệnh.', $d);
        $this->sortQ($L, 'Kéo mỗi câu vào nhóm ĐÚNG hoặc SAI (sức khỏe).', [['We should drink water every day.', 'ĐÚNG'], ['Wash hands before meals.', 'ĐÚNG'], ['Smoking is good for lungs.', 'SAI'], ['Staying up late is healthy.', 'SAI']], 'Uống nước, rửa tay tốt cho sức khỏe; hút thuốc, thức khuya có hại.', $d);
        $this->fill($L, '"Ho" trong tiếng Anh là ___.', [[0, 'cough']], '"Ho" = cough.', $d);
        $this->fill($L, 'I have a sore ___. (Tôi bị đau họng.)', [[0, 'throat']], '"Đau họng" = sore throat.', $d);
        $this->fill($L, '"Hiệu thuốc" trong tiếng Anh là ___.', [[0, 'pharmacy']], '"Hiệu thuốc" = pharmacy.', $d);
        $this->fill($L, 'She ___ a cold last week. (Cô ấy bị cảm lạnh tuần trước.)', [[0, 'caught']], '"Bị cảm" = catch a cold (quá khứ: caught).', $d);
        $this->fill($L, 'You should ___ your hands before eating. (Bạn nên rửa tay trước khi ăn.)', [[0, 'wash']], '"Rửa tay" = wash hands.', $d);
    }

    // 16. en-tu-vung-lop-7-lop-7-2 (g7, trung_binh) - Từ vựng du lịch
    private function seedEnTravelVocab2(): void
    {
        $L = 'en-tu-vung-lop-7-lop-7-2'; $d = 'trung_binh';
        $this->quiz($L, '"Flight" nghĩa là gì?', ['chuyến bay', 'chuyến tàu', 'chuyến xe', 'hành trình'], 0, '"Flight" = chuyến bay.', $d);
        $this->quiz($L, '"Backpack" nghĩa là gì?', ['vali', 'ba lô', 'túi xách', 'ví'], 1, '"Backpack" = ba lô.', $d);
        $this->quiz($L, '"Booking" nghĩa là gì?', ['hủy phòng', 'thanh toán', 'đặt chỗ', 'nhận phòng'], 2, '"Booking" = đặt chỗ.', $d);
        $this->quiz($L, '"Delay" nghĩa là gì?', ['khởi hành', 'hạ cánh', 'quá cảnh', 'trì hoãn'], 3, '"Delay" = trì hoãn (chuyến bay bị hoãn).', $d);
        $this->quiz($L, '"Take photos" nghĩa là gì?', ['chụp ảnh', 'quay phim', 'mua quà', 'thuê xe'], 0, '"Take photos" = chụp ảnh.', $d);
        $this->matching($L, 'Nối mỗi từ du lịch với nghĩa của nó (nhóm 1).', [['flight', 'chuyến bay'], ['luggage', 'hành lý'], ['backpack', 'ba lô'], ['camera', 'máy ảnh']], 'flight = chuyến bay, luggage = hành lý, backpack = ba lô, camera = máy ảnh.', $d);
        $this->matching($L, 'Nối mỗi từ ở sân bay với nghĩa của nó.', [['departure', 'khởi hành'], ['arrival', 'đến nơi'], ['delay', 'trì hoãn'], ['cancel', 'hủy']], 'departure = khởi hành, arrival = đến nơi, delay = trì hoãn, cancel = hủy.', $d);
        $this->matching($L, 'Nối mỗi địa điểm du lịch với nghĩa của nó.', [['museum', 'bảo tàng'], ['temple', 'đền/chùa'], ['market', 'chợ'], ['park', 'công viên']], 'museum = bảo tàng, temple = đền/chùa, market = chợ, park = công viên.', $d);
        $this->matching($L, 'Nối mỗi cụm động từ du lịch với nghĩa của nó.', [['book a room', 'đặt phòng'], ['take photos', 'chụp ảnh'], ['buy souvenirs', 'mua quà lưu niệm'], ['try local food', 'thử món ăn địa phương']], 'book a room = đặt phòng, take photos = chụp ảnh, buy souvenirs = mua quà lưu niệm.', $d);
        $this->matching($L, 'Nối mỗi câu với nghĩa của nó (du lịch).', [['The flight is delayed.', 'Chuyến bay bị hoãn.'], ['We stayed in a hotel.', 'Chúng tôi ở khách sạn.'], ['She bought souvenirs.', 'Cô ấy đã mua quà lưu niệm.'], ['They took many photos.', 'Họ đã chụp nhiều ảnh.']], 'Đọc cả câu để nối đúng nghĩa.', $d);
        $this->sortQ($L, 'Kéo mỗi từ vào nhóm TRƯỚC CHUYẾN ĐI hoặc TRONG CHUYẾN ĐI.', [['pack', 'TRƯỚC CHUYẾN ĐI'], ['book', 'TRƯỚC CHUYẾN ĐI'], ['swim', 'TRONG CHUYẾN ĐI'], ['take photos', 'TRONG CHUYẾN ĐI']], 'Đóng gói, đặt chỗ trước chuyến đi; bơi, chụp ảnh trong chuyến đi.', $d);
        $this->sortQ($L, 'Kéo mỗi từ vào nhóm Ở SÂN BAY hoặc Ở KHÁCH SẠN.', [['passport', 'Ở SÂN BAY'], ['boarding pass', 'Ở SÂN BAY'], ['room key', 'Ở KHÁCH SẠN'], ['reception', 'Ở KHÁCH SẠN']], 'Passport/boarding pass ở sân bay; room key/reception ở khách sạn.', $d);
        $this->sortQ($L, 'Kéo mỗi hoạt động vào nhóm MIỄN PHÍ hoặc TỐN TIỀN.', [['walk on the beach', 'MIỄN PHÍ'], ['swim in the sea', 'MIỄN PHÍ'], ['buy tickets', 'TỐN TIỀN'], ['stay in a hotel', 'TỐN TIỀN']], 'Đi dạo, bơi biển miễn phí; mua vé, ở khách sạn tốn tiền.', $d);
        $this->sortQ($L, 'Kéo mỗi từ vào nhóm PHƯƠNG TIỆN CÔNG CỘNG hoặc CÁ NHÂN.', [['bus', 'PHƯƠNG TIỆN CÔNG CỘNG'], ['train', 'PHƯƠNG TIỆN CÔNG CỘNG'], ['car', 'CÁ NHÂN'], ['bike', 'CÁ NHÂN']], 'Bus/train là phương tiện công cộng; car/bike là cá nhân.', $d);
        $this->sortQ($L, 'Kéo mỗi câu vào nhóm ĐÚNG hoặc SAI (du lịch).', [['We need passports to travel abroad.', 'ĐÚNG'], ['Hotels give you a room key.', 'ĐÚNG'], ['You can board without a ticket.', 'SAI'], ['Luggage flies with you on the plane.', 'SAI']], 'Đi nước ngoài cần hộ chiếu; lên máy bay cần vé.', $d);
        $this->fill($L, '"Chuyến bay" trong tiếng Anh là ___.', [[0, 'flight']], '"Chuyến bay" = flight.', $d);
        $this->fill($L, 'Our flight was ___. (Chuyến bay của chúng tôi bị hoãn.)', [[0, 'delayed']], '"Bị hoãn" = delayed.', $d);
        $this->fill($L, '"Quà lưu niệm" trong tiếng Anh là ___.', [[0, 'souvenir']], '"Quà lưu niệm" = souvenir.', $d);
        $this->fill($L, 'We ___ photos at the beach. (Chúng tôi đã chụp ảnh ở bãi biển.)', [[0, 'took']], '"Chụp ảnh" = take photos (quá khứ: took).', $d);
        $this->fill($L, 'The ___ showed us around the museum. (Hướng dẫn viên đưa chúng tôi đi quanh bảo tàng.)', [[0, 'tour guide']], '"Hướng dẫn viên" = tour guide.', $d);
    }

    // 17. en-tu-vung-lop-7-lop-8-1 (g8, trung_binh) - Môi trường
    private function seedEnEnvironment(): void
    {
        $L = 'en-tu-vung-lop-7-lop-8-1'; $d = 'trung_binh';
        $this->quiz($L, '"Climate change" nghĩa là gì?', ['biến đổi khí hậu', 'ô nhiễm không khí', 'hiệu ứng nhà kính', 'mưa axit'], 0, '"Climate change" = biến đổi khí hậu.', $d);
        $this->quiz($L, '"Endangered" nghĩa là gì?', ['tuyệt chủng', 'có nguy cơ tuyệt chủng', 'quý hiếm', 'được bảo vệ'], 1, '"Endangered" = có nguy cơ tuyệt chủng.', $d);
        $this->quiz($L, '"Solar energy" nghĩa là gì?', ['năng lượng gió', 'năng lượng nước', 'năng lượng mặt trời', 'năng lượng hạt nhân'], 2, '"Solar energy" = năng lượng mặt trời.', $d);
        $this->quiz($L, '"Wildlife" nghĩa là gì?', ['thực vật', 'khí hậu', 'môi trường', 'động vật hoang dã'], 3, '"Wildlife" = động vật hoang dã.', $d);
        $this->quiz($L, '"Save water" nghĩa là gì?', ['tiết kiệm nước', 'lãng phí nước', 'ô nhiễm nước', 'đun nước'], 0, '"Save water" = tiết kiệm nước.', $d);
        $this->matching($L, 'Nối mỗi động từ môi trường với nghĩa của nó.', [['litter', 'xả rác'], ['recycle', 'tái chế'], ['protect', 'bảo vệ'], ['pollute', 'gây ô nhiễm']], 'litter = xả rác, recycle = tái chế, protect = bảo vệ, pollute = gây ô nhiễm.', $d);
        $this->matching($L, 'Nối mỗi vấn đề môi trường với nghĩa của nó.', [['climate change', 'biến đổi khí hậu'], ['global warming', 'nóng lên toàn cầu'], ['deforestation', 'phá rừng'], ['air pollution', 'ô nhiễm không khí']], 'climate change = biến đổi khí hậu, global warming = nóng lên toàn cầu, deforestation = phá rừng.', $d);
        $this->matching($L, 'Nối mỗi cụm từ môi trường với nghĩa của nó.', [['solar energy', 'năng lượng mặt trời'], ['wind energy', 'năng lượng gió'], ['plastic bag', 'túi ni lông'], ['rubbish bin', 'thùng rác']], 'solar energy = năng lượng mặt trời, wind energy = năng lượng gió, plastic bag = túi ni lông.', $d);
        $this->matching($L, 'Nối mỗi từ về thiên nhiên với nghĩa của nó.', [['endangered', 'có nguy cơ tuyệt chủng'], ['wildlife', 'động vật hoang dã'], ['nature', 'thiên nhiên'], ['ecosystem', 'hệ sinh thái']], 'endangered = có nguy cơ tuyệt chủng, wildlife = động vật hoang dã, nature = thiên nhiên.', $d);
        $this->matching($L, 'Nối mỗi câu bảo vệ môi trường với nghĩa của nó.', [['Don\'t litter!', 'Đừng xả rác!'], ['Plant more trees.', 'Hãy trồng thêm cây.'], ['Save water every day.', 'Hãy tiết kiệm nước mỗi ngày.'], ['Recycle plastic bottles.', 'Hãy tái chế chai nhựa.']], 'Đọc cả câu để nối đúng nghĩa.', $d);
        $this->sortQ($L, 'Kéo mỗi hành động vào nhóm TIẾT KIỆM hoặc LÃNG PHÍ.', [['turn off lights', 'TIẾT KIỆM'], ['save water', 'TIẾT KIỆM'], ['leave tap running', 'LÃNG PHÍ'], ['waste paper', 'LÃNG PHÍ']], 'Tắt đèn, tiết kiệm nước là tiết kiệm; để vòi chảy, lãng phí giấy là lãng phí.', $d);
        $this->sortQ($L, 'Kéo mỗi từ vào nhóm NĂNG LƯỢNG SẠCH hoặc KHÔNG SẠCH.', [['solar', 'NĂNG LƯỢNG SẠCH'], ['wind', 'NĂNG LƯỢNG SẠCH'], ['coal', 'KHÔNG SẠCH'], ['oil', 'KHÔNG SẠCH']], 'Năng lượng mặt trời/gió sạch; than/dầu gây ô nhiễm.', $d);
        $this->sortQ($L, 'Kéo mỗi vật vào nhóm TÁI CHẾ ĐƯỢC hoặc KHÓ TÁI CHẾ.', [['newspaper', 'TÁI CHẾ ĐƯỢC'], ['glass bottle', 'TÁI CHẾ ĐƯỢC'], ['plastic bag', 'KHÓ TÁI CHẾ'], ['foam box', 'KHÓ TÁI CHẾ']], 'Giấy báo, chai thủy tinh tái chế được; túi ni lông, hộp xốp khó tái chế.', $d);
        $this->sortQ($L, 'Kéo mỗi từ vào nhóm NGUYÊN NHÂN hoặc HẬU QUẢ ô nhiễm.', [['litter', 'NGUYÊN NHÂN'], ['smoke', 'NGUYÊN NHÂN'], ['dirty air', 'HẬU QUẢ'], ['dead fish', 'HẬU QUẢ']], 'Xả rác, khói là nguyên nhân; không khí bẩn, cá chết là hậu quả.', $d);
        $this->sortQ($L, 'Kéo mỗi câu vào nhóm ĐÚNG hoặc SAI (môi trường).', [['Trees clean the air.', 'ĐÚNG'], ['Solar energy is clean.', 'ĐÚNG'], ['Plastic bags decay quickly.', 'SAI'], ['Littering keeps streets clean.', 'SAI']], 'Cây xanh làm sạch không khí; túi ni lông phân hủy rất lâu.', $d);
        $this->fill($L, '"Biến đổi khí hậu" trong tiếng Anh là ___.', [[0, 'climate change']], '"Biến đổi khí hậu" = climate change.', $d);
        $this->fill($L, 'We must stop ___. (Chúng ta phải ngăn chặn nạn phá rừng.)', [[0, 'deforestation']], '"Phá rừng" = deforestation.', $d);
        $this->fill($L, '"Năng lượng mặt trời" trong tiếng Anh là ___.', [[0, 'solar energy']], '"Năng lượng mặt trời" = solar energy.', $d);
        $this->fill($L, 'Many animals are ___. (Nhiều loài động vật đang có nguy cơ tuyệt chủng.)', [[0, 'endangered']], '"Có nguy cơ tuyệt chủng" = endangered.', $d);
        $this->fill($L, 'Turn off the lights to save ___. (Hãy tắt đèn để tiết kiệm điện.)', [[0, 'electricity']], '"Điện" = electricity.', $d);
    }

    // 18. en-tu-vung-lop-7-lop-8-2 (g8, trung_binh) - Cộng đồng
    private function seedEnCommunity(): void
    {
        $L = 'en-tu-vung-lop-7-lop-8-2'; $d = 'trung_binh';
        $this->quiz($L, '"Fundraising" nghĩa là gì?', ['gây quỹ', 'quyên góp đồ', 'tình nguyện', 'từ thiện'], 0, '"Fundraising" = gây quỹ.', $d);
        $this->quiz($L, '"Homeless" nghĩa là gì?', ['giàu có', 'vô gia cư', 'tàn tật', 'mồ côi'], 1, '"Homeless" = vô gia cư (không nhà ở).', $d);
        $this->quiz($L, 'Từ nào có nghĩa là "quyên góp (tiền, đồ)"?', ['help', 'donate', 'share', 'give'], 1, '"Donate" = quyên góp.', $d);
        $this->quiz($L, '"Elderly" nghĩa là gì?', ['trẻ nhỏ', 'thanh niên', 'người cao tuổi', 'người tàn tật'], 2, '"Elderly" = người cao tuổi.', $d);
        $this->quiz($L, '"Clean-up" (hoạt động dọn dẹp) nghĩa là gì?', ['xây dựng', 'sửa chữa', 'trồng cây', 'dọn dẹp'], 3, '"Clean-up" = hoạt động dọn dẹp.', $d);
        $this->matching($L, 'Nối mỗi từ về cộng đồng với nghĩa của nó (nhóm 1).', [['volunteer', 'tình nguyện viên'], ['donate', 'quyên góp'], ['charity', 'từ thiện'], ['community', 'cộng đồng']], 'volunteer = tình nguyện viên, donate = quyên góp, charity = từ thiện, community = cộng đồng.', $d);
        $this->matching($L, 'Nối mỗi từ về cộng đồng với nghĩa của nó (nhóm 2).', [['fundraising', 'gây quỹ'], ['homeless', 'vô gia cư'], ['elderly', 'người cao tuổi'], ['orphan', 'trẻ mồ côi']], 'fundraising = gây quỹ, homeless = vô gia cư, elderly = người cao tuổi, orphan = trẻ mồ côi.', $d);
        $this->matching($L, 'Nối mỗi động từ giúp đỡ với nghĩa của nó.', [['help', 'giúp đỡ'], ['share', 'chia sẻ'], ['support', 'hỗ trợ'], ['join', 'tham gia']], 'help = giúp đỡ, share = chia sẻ, support = hỗ trợ, join = tham gia.', $d);
        $this->matching($L, 'Nối mỗi câu với nghĩa của nó (cộng đồng).', [['We help the elderly.', 'Chúng tôi giúp đỡ người cao tuổi.'], ['They donated clothes.', 'Họ đã quyên góp quần áo.'], ['She joined the club.', 'Cô ấy đã tham gia câu lạc bộ.'], ['He volunteers on Sundays.', 'Anh ấy làm tình nguyện vào Chủ nhật.']], 'Đọc cả câu để nối đúng nghĩa.', $d);
        $this->matching($L, 'Nối mỗi tính từ chỉ phẩm chất với nghĩa của nó.', [['kind', 'tốt bụng'], ['generous', 'hào phóng'], ['helpful', 'hay giúp đỡ'], ['caring', 'chu đáo']], 'kind = tốt bụng, generous = hào phóng, helpful = hay giúp đỡ, caring = chu đáo.', $d);
        $this->sortQ($L, 'Kéo mỗi từ vào nhóm TÍNH TỪ CHỈ PHẨM CHẤT hoặc DANH TỪ CHỈ NGƯỜI.', [['kind', 'TÍNH TỪ CHỈ PHẨM CHẤT'], ['generous', 'TÍNH TỪ CHỈ PHẨM CHẤT'], ['volunteer', 'DANH TỪ CHỈ NGƯỜI'], ['donor', 'DANH TỪ CHỈ NGƯỜI']], 'Kind/generous là tính từ; volunteer/donor là danh từ chỉ người.', $d);
        $this->sortQ($L, 'Kéo mỗi hoạt động tình nguyện vào nhóm NGOÀI TRỜI hoặc TRONG NHÀ.', [['clean the park', 'NGOÀI TRỜI'], ['plant trees', 'NGOÀI TRỜI'], ['teach kids', 'TRONG NHÀ'], ['visit patients', 'TRONG NHÀ']], 'Dọn công viên, trồng cây ngoài trời; dạy học, thăm bệnh nhân trong nhà.', $d);
        $this->sortQ($L, 'Kéo mỗi từ vào nhóm CHO ĐI hoặc NHẬN LẠI.', [['donate', 'CHO ĐI'], ['share', 'CHO ĐI'], ['receive', 'NHẬN LẠI'], ['thank', 'NHẬN LẠI']], 'Donate/share là cho đi; receive/thank là nhận lại.', $d);
        $this->sortQ($L, 'Kéo mỗi câu vào nhóm ĐÚNG hoặc SAI (cộng đồng).', [['Volunteers help others for free.', 'ĐÚNG'], ['Charity helps poor people.', 'ĐÚNG'], ['Donating means taking things.', 'SAI'], ['Community means living alone.', 'SAI']], 'Tình nguyện giúp người miễn phí; từ thiện giúp người nghèo.', $d);
        $this->sortQ($L, 'Kéo mỗi cụm từ vào nhóm QUYÊN GÓP TIỀN hoặc QUYÊN GÓP ĐỒ.', [['raise funds', 'QUYÊN GÓP TIỀN'], ['donate money', 'QUYÊN GÓP TIỀN'], ['donate clothes', 'QUYÊN GÓP ĐỒ'], ['collect books', 'QUYÊN GÓP ĐỒ']], 'Raise funds/donate money là tiền; donate clothes/collect books là đồ.', $d);
        $this->fill($L, '"Gây quỹ" trong tiếng Anh là ___.', [[0, 'fundraising']], '"Gây quỹ" = fundraising.', $d);
        $this->fill($L, 'We raised money for the ___. (Chúng tôi đã gây quỹ cho trẻ mồ côi.)', [[0, 'orphans']], '"Trẻ mồ côi" = orphans.', $d);
        $this->fill($L, '"Người cao tuổi" trong tiếng Anh là the ___.', [[0, 'elderly']], '"Người cao tuổi" = the elderly.', $d);
        $this->fill($L, 'She is very ___. (Cô ấy rất hào phóng.)', [[0, 'generous']], '"Hào phóng" = generous.', $d);
        $this->fill($L, 'They ___ the beach last Sunday. (Họ đã dọn dẹp bãi biển Chủ nhật trước.)', [[0, 'cleaned up']], '"Dọn dẹp" = clean up (quá khứ: cleaned up).', $d);
    }

    // 19. tieng-anh-thpt-10-cac-thi-co-ban-1 (g10, trung_binh) - HTĐ & HTTD
    private function seedEnTenseReview1(): void
    {
        $L = 'tieng-anh-thpt-10-cac-thi-co-ban-1'; $d = 'trung_binh';
        $this->quiz($L, 'Choose the correct answer: She ___ her teeth twice a day. (Cô ấy đánh răng hai lần mỗi ngày.)', ['brush', 'brushes', 'brushing', 'is brush'], 1, 'She → brushes (thói quen, hiện tại đơn).', $d);
        $this->quiz($L, 'Choose the correct answer: Listen! Someone ___ at the door. (Nghe kìa! Có người đang gõ cửa.)', ['knock', 'knocks', 'is knocking', 'are knocking'], 2, 'Listen! báo hiệu hành động đang diễn ra: is knocking.', $d);
        $this->quiz($L, 'Choose the correct answer: I ___ to music every day. (Tôi nghe nhạc mỗi ngày.)', ['listen', 'listens', 'listening', 'am listen'], 0, 'I + every day → listen (hiện tại đơn).', $d);
        $this->quiz($L, 'Choose the correct answer: ___ she working now? (Bây giờ cô ấy đang làm việc à?)', ['Does', 'Do', 'Are', 'Is'], 3, 'Câu hỏi tiếp diễn với she: Is she working?', $d);
        $this->quiz($L, 'Choose the correct answer: They ___ TV every evening. (Họ xem TV mỗi tối.)', ['watch', 'watches', 'watching', 'are watch'], 0, 'They + every evening → watch (hiện tại đơn).', $d);
        $this->matching($L, 'Match each sentence with the correct tense (set 2).', [['She reads daily.', 'present simple'], ['He is reading now.', 'present continuous'], ['They swim often.', 'present simple'], ['We are swimming now.', 'present continuous']], 'daily/often → present simple; now → present continuous.', $d);
        $this->matching($L, 'Match each verb with its -ing form (set 2).', [['die', 'dying'], ['lie', 'lying'], ['tie', 'tying'], ['swim', 'swimming']], 'die→dying, lie→lying, tie→tying, swim→swimming.', $d);
        $this->matching($L, 'Match each time marker with the tense (set 2).', [['at present', 'present continuous'], ['once a week', 'present simple'], ['right now', 'present continuous'], ['on Mondays', 'present simple']], 'at present/right now → tiếp diễn; once a week/on Mondays → hiện tại đơn.', $d);
        $this->matching($L, 'Match each sentence with its Vietnamese meaning (set 2).', [['She is studying.', 'Cô ấy đang học bài.'], ['He plays chess.', 'Anh ấy chơi cờ.'], ['They are dancing.', 'Họ đang nhảy.'], ['We like tea.', 'Chúng tôi thích trà.']], 'is studying/are dancing = đang...; plays/like = thói quen.', $d);
        $this->matching($L, 'Match each mistake with its correction (tenses).', [['He is go.', 'He is going.'], ['They watches TV.', 'They watch TV.'], ['She are happy.', 'She is happy.'], ['I is reading.', 'I am reading.']], 'Be + V-ing; they + V nguyên thể; she + is; I + am.', $d);
        $this->sortQ($L, 'Drag each sentence into HABIT or ACTION NOW.', [['I walk to school.', 'HABIT'], ['She drinks tea daily.', 'HABIT'], ['I am walking now.', 'ACTION NOW'], ['She is drinking tea.', 'ACTION NOW']], 'Hiện tại đơn = thói quen; tiếp diễn = đang diễn ra.', $d);
        $this->sortQ($L, 'Drag each verb form into CORRECT or INCORRECT (set 2).', [['She goes.', 'CORRECT'], ['They play.', 'CORRECT'], ['She go.', 'INCORRECT'], ['He don\'t.', 'INCORRECT']], 'She→goes; they→play; he→doesn\'t.', $d);
        $this->sortQ($L, 'Drag each sentence into WITH -ING or WITHOUT -ING.', [['She is singing.', 'WITH -ING'], ['They are running.', 'WITH -ING'], ['She sings.', 'WITHOUT -ING'], ['They run.', 'WITHOUT -ING']], 'Tiếp diễn có V-ing; hiện tại đơn không.', $d);
        $this->sortQ($L, 'Drag each word into SIGNAL OF PRESENT SIMPLE or PRESENT CONTINUOUS (set 2).', [['often', 'PRESENT SIMPLE'], ['always', 'PRESENT SIMPLE'], ['currently', 'PRESENT CONTINUOUS'], ['at the moment', 'PRESENT CONTINUOUS']], 'often/always → hiện tại đơn; currently/at the moment → tiếp diễn.', $d);
        $this->sortQ($L, 'Drag each sentence into AFFIRMATIVE or QUESTION.', [['She sings.', 'AFFIRMATIVE'], ['They dance.', 'AFFIRMATIVE'], ['Does she sing?', 'QUESTION'], ['Are they dancing?', 'QUESTION']], 'Câu hỏi đảo trợ động từ/be lên đầu.', $d);
        $this->fill($L, 'He ___ (read) a book now.', [[0, 'is reading']], 'now → he is reading.', $d);
        $this->fill($L, 'They ___ (have) lunch at noon every day.', [[0, 'have']], 'every day → they have.', $d);
        $this->fill($L, 'Listen! The birds ___ (sing).', [[0, 'are singing']], 'Listen! → the birds are singing.', $d);
        $this->fill($L, 'My dad ___ (not/smoke).', [[0, 'does not smoke']], 'My dad = he → does not smoke.', $d);
        $this->fill($L, '___ she ___ (work) at the moment? (Cô ấy đang làm việc à?)', [[0, 'Is'], [1, 'working']], 'Is she working at the moment?', $d);
    }

    // 20. tieng-anh-thpt-10-cac-thi-co-ban-2 (g10, trung_binh) - QKĐ & TLĐ
    private function seedEnTenseReview2(): void
    {
        $L = 'tieng-anh-thpt-10-cac-thi-co-ban-2'; $d = 'trung_binh';
        $this->quiz($L, 'Choose the correct answer: We ___ to Da Lat last year. (Năm ngoái chúng tôi đã đi Đà Lạt.)', ['go', 'went', 'will go', 'goes'], 1, 'last year → went (quá khứ đơn).', $d);
        $this->quiz($L, 'Choose the correct answer: She ___ a nice dress last week. (Cô ấy đã mua một chiếc váy đẹp tuần trước.)', ['buy', 'bought', 'will buy', 'buys'], 1, 'last week → bought.', $d);
        $this->quiz($L, 'Choose the correct answer: He ___ late yesterday. (Hôm qua anh ấy đã đến muộn.)', ['come', 'comes', 'came', 'will come'], 2, 'yesterday → came.', $d);
        $this->quiz($L, 'Choose the correct answer: I think she ___ tired tomorrow. (Tôi nghĩ ngày mai cô ấy sẽ mệt.)', ['is', 'was', 'will be', 'be'], 2, 'tomorrow + dự đoán → will be.', $d);
        $this->quiz($L, 'Choose the correct answer: A: I\'ve decided. B: OK, I ___ you. (Tôi sẽ giúp bạn.)', ['help', 'helped', 'will help', 'am help'], 2, 'Quyết định ngay lúc nói → will help.', $d);
        $this->matching($L, 'Match each verb with its past form (set 2).', [['eat', 'ate'], ['drink', 'drank'], ['sing', 'sang'], ['begin', 'began']], 'eat→ate, drink→drank, sing→sang, begin→began.', $d);
        $this->matching($L, 'Match each sentence with the correct tense (set 2).', [['She left yesterday.', 'past simple'], ['I will leave tomorrow.', 'future simple'], ['They played well.', 'past simple'], ['We will win.', 'future simple']], 'yesterday → past simple; tomorrow → future simple.', $d);
        $this->matching($L, 'Match each time marker with the tense (set 2).', [['last week', 'past simple'], ['next month', 'future simple'], ['in 2020', 'past simple'], ['soon', 'future simple']], 'last/in 2020 → quá khứ; next/soon → tương lai.', $d);
        $this->matching($L, 'Match each use with the tense (set 2).', [['a finished action', 'past simple'], ['a spontaneous decision', 'future simple'], ['a past habit', 'past simple'], ['a promise', 'future simple']], 'Hành động đã xong → quá khứ đơn; quyết định tức thì/lời hứa → tương lai đơn.', $d);
        $this->matching($L, 'Match each sentence with its Vietnamese meaning (set 2).', [['She bought a car.', 'Cô ấy đã mua một chiếc xe.'], ['I will call you.', 'Tôi sẽ gọi cho bạn.'], ['They left early.', 'Họ đã đi sớm.'], ['We will meet soon.', 'Chúng ta sẽ sớm gặp nhau.']], 'bought/left → đã...; will call/will meet → sẽ...', $d);
        $this->sortQ($L, 'Drag each verb into REGULAR or IRREGULAR past (set 2).', [['wanted', 'REGULAR'], ['played', 'REGULAR'], ['went', 'IRREGULAR'], ['ate', 'IRREGULAR']], 'wanted/played thêm -ed; went/ate bất quy tắc.', $d);
        $this->sortQ($L, 'Drag each sentence into PAST SIMPLE or FUTURE SIMPLE (set 2).', [['He arrived late.', 'PAST SIMPLE'], ['She finished early.', 'PAST SIMPLE'], ['He will arrive soon.', 'FUTURE SIMPLE'], ['She will finish soon.', 'FUTURE SIMPLE']], 'V-ed → quá khứ đơn; will + V → tương lai đơn.', $d);
        $this->sortQ($L, 'Drag each sentence into CORRECT or INCORRECT (set 2).', [['They will come.', 'CORRECT'], ['She goed home.', 'INCORRECT'], ['He didn\'t went.', 'INCORRECT'], ['I will helps.', 'INCORRECT']], 'go→went; didn\'t + V nguyên thể; will + V nguyên thể.', $d);
        $this->sortQ($L, 'Drag each phrase into PAST MARKER or FUTURE MARKER (set 2).', [['ago', 'PAST MARKER'], ['last night', 'PAST MARKER'], ['next week', 'FUTURE MARKER'], ['tomorrow', 'FUTURE MARKER']], 'ago/last → quá khứ; next/tomorrow → tương lai.', $d);
        $this->sortQ($L, 'Drag each verb into PAST FORM or BASE FORM.', [['went', 'PAST FORM'], ['ate', 'PAST FORM'], ['go', 'BASE FORM'], ['eat', 'BASE FORM']], 'went/ate là quá khứ; go/eat là nguyên thể.', $d);
        $this->fill($L, 'We ___ (visit) Hue last summer.', [[0, 'visited']], 'last summer → visited.', $d);
        $this->fill($L, 'He ___ (buy) a new bike yesterday.', [[0, 'bought']], 'yesterday → bought.', $d);
        $this->fill($L, 'They ___ (arrive) tomorrow morning.', [[0, 'will arrive']], 'tomorrow → will arrive.', $d);
        $this->fill($L, 'She ___ (not/go) out last night.', [[0, 'did not go']], 'Phủ định quá khứ: did not + go.', $d);
        $this->fill($L, 'I ___ (help) you with your homework tonight. (promise)', [[0, 'will help']], 'Lời hứa → will help.', $d);
    }

    // 21. tieng-anh-thpt-10-cau-dieu-kien-1-1 (g10, trung_binh) - Câu ĐK loại 1 (1)
    private function seedEnConditional1a(): void
    {
        $L = 'tieng-anh-thpt-10-cau-dieu-kien-1-1'; $d = 'trung_binh';
        $this->quiz($L, 'Choose the correct answer: If you study hard, you ___ the exam. (Nếu bạn học chăm, bạn sẽ đỗ.)', ['pass', 'will pass', 'passed', 'passing'], 1, 'Loại 1: If + hiện tại đơn, will + V.', $d);
        $this->quiz($L, 'Choose the correct answer: If they ___, we will wait. (Nếu họ đến muộn, chúng tôi sẽ đợi.)', ['come late', 'will come late', 'comes late', 'came late'], 2, 'Mệnh đề if: hiện tại đơn (they come late).', $d);
        $this->quiz($L, 'Choose the correct answer: If you ___ now, you will catch the bus. (Nếu bạn đi ngay, bạn sẽ kịp xe.)', ['leave', 'will leave', 'left', 'leaving'], 0, 'If + leave (hiện tại đơn), will catch.', $d);
        $this->quiz($L, 'Which sentence is a correct type 1 conditional?', ['If it will rain, we stay.', 'If it rains, we will stay.', 'If it rained, we stay.', 'If it rains, we stay.'], 1, 'Chỉ "If it rains, we will stay." đúng cấu trúc loại 1.', $d);
        $this->quiz($L, 'Choose the correct answer: If he ___, tell him I called. (Nếu anh ấy đến, bảo rằng tôi đã gọi.)', ['come', 'will come', 'comes', 'came'], 2, 'He → comes (mệnh đề if, hiện tại đơn).', $d);
        $this->matching($L, 'Match each part to complete the conditional (set 2).', [['If it rains,', 'we will stay home.'], ['If she comes,', 'we will start.'], ['If you hurry,', 'you will catch it.'], ['If he calls,', 'tell me.']], 'If + hiện tại đơn, will + V (hoặc mệnh lệnh).', $d);
        $this->matching($L, 'Match each clause with its name (set 2).', [['If you go', 'if-clause'], ['we will follow', 'main clause'], ['If she is tired', 'if-clause'], ['I will rest', 'main clause']], 'Mệnh đề bắt đầu bằng if là if-clause; còn lại là main clause.', $d);
        $this->matching($L, 'Match each sentence with its meaning (conditional).', [['If you heat ice, it melts.', 'general truth'], ['If you heat ice, it will melt.', 'possible future'], ['If it rains, we get wet.', 'general truth'], ['If it rains, we will get wet.', 'possible future']], 'Loại 0 = chân lý; loại 1 = khả năng tương lai.', $d);
        $this->matching($L, 'Match each error with its correction (type 1).', [['If it will rain, we go.', 'If it rains, we will go.'], ['If she comes, we goes.', 'If she comes, we will go.'], ['If you will hurry, you catch.', 'If you hurry, you will catch.'], ['If he is late, we waits.', 'If he is late, we will wait.']], 'Mệnh đề if không dùng will; mệnh đề chính dùng will.', $d);
        $this->matching($L, 'Match each Vietnamese meaning with the English sentence (conditional).', [['Nếu trời đẹp, chúng ta sẽ đi.', 'If the weather is nice, we will go.'], ['Nếu bạn cần, hãy gọi tôi.', 'If you need, call me.'], ['Nếu cô ấy đến muộn, cô ấy sẽ lỡ xe.', 'If she is late, she will miss the bus.'], ['Nếu anh ấy học, anh ấy sẽ đỗ.', 'If he studies, he will pass.']], 'Đọc kỹ cả hai mệnh đề để nối đúng.', $d);
        $this->sortQ($L, 'Drag each sentence into TYPE 1 or TYPE 2.', [['If I have money, I will buy it.', 'TYPE 1'], ['If she comes, we will eat.', 'TYPE 1'], ['If I had money, I would buy it.', 'TYPE 2'], ['If she came, we would eat.', 'TYPE 2']], 'Loại 1: If + hiện tại, will; loại 2: If + quá khứ, would.', $d);
        $this->sortQ($L, 'Drag each verb form into IF-CLAUSE or MAIN CLAUSE (set 2).', [['rains', 'IF-CLAUSE'], ['studies', 'IF-CLAUSE'], ['will stay', 'MAIN CLAUSE'], ['will pass', 'MAIN CLAUSE']], 'If-clause: hiện tại đơn; main clause: will + V.', $d);
        $this->sortQ($L, 'Drag each sentence into CORRECT or INCORRECT type 1 (set 2).', [['If you go, I will go.', 'CORRECT'], ['If she comes, we will eat.', 'CORRECT'], ['If you will go, I go.', 'INCORRECT'], ['If it rains, we stay.', 'INCORRECT']], 'Mệnh đề if không dùng will; mệnh đề chính phải có will.', $d);
        $this->sortQ($L, 'Drag each sentence into REAL or UNREAL condition.', [['If she comes, we will start.', 'REAL'], ['If you try, you will win.', 'REAL'], ['If I were you, I would go.', 'UNREAL'], ['If he had time, he would help.', 'UNREAL']], 'Loại 1 = điều kiện có thật; loại 2 = không có thật.', $d);
        $this->sortQ($L, 'Drag each part into BEFORE COMMA or AFTER COMMA (set 2).', [['If you are free', 'BEFORE COMMA'], ['If it stops raining', 'BEFORE COMMA'], ['call me', 'AFTER COMMA'], ['we will go out', 'AFTER COMMA']], 'Mệnh đề if đứng đầu, sau đó là dấu phẩy.', $d);
        $this->fill($L, 'If you ___ (work) hard, you will succeed.', [[0, 'work']], 'If + work (hiện tại đơn).', $d);
        $this->fill($L, 'She will be late if she ___ (not/hurry).', [[0, 'does not hurry']], 'She → does not hurry.', $d);
        $this->fill($L, 'If they invite me, I ___ (come).', [[0, 'will come']], 'Mệnh đề chính: will come.', $d);
        $this->fill($L, 'We ___ (cancel) the trip if it rains.', [[0, 'will cancel']], 'Mệnh đề chính: will cancel.', $d);
        $this->fill($L, 'If he ___ (be) free, he will join us.', [[0, 'is']], 'He → is.', $d);
    }

    // 22. tieng-anh-thpt-10-cau-dieu-kien-1-2 (g10, trung_binh) - Câu ĐK loại 1 (2)
    private function seedEnConditional1b(): void
    {
        $L = 'tieng-anh-thpt-10-cau-dieu-kien-1-2'; $d = 'trung_binh';
        $this->quiz($L, 'Choose the correct answer: He will pass if he ___. (Anh ấy sẽ đỗ nếu học chăm.)', ['study', 'studies', 'will study', 'studied'], 1, 'He → studies (mệnh đề if, hiện tại đơn).', $d);
        $this->quiz($L, 'Choose the correct answer: If it is sunny, we ___ swimming. (Nếu trời nắng, chúng ta sẽ đi bơi.)', ['go', 'will go', 'went', 'going'], 1, 'Mệnh đề chính: will go.', $d);
        $this->quiz($L, 'Choose the correct answer: They will cancel if it ___. (Họ sẽ hủy nếu trời mưa.)', ['rain', 'rains', 'will rain', 'rained'], 1, 'It → rains (mệnh đề if).', $d);
        $this->quiz($L, 'Choose the correct answer: If she ___, I will be happy. (Nếu cô ấy gọi, tôi sẽ vui.)', ['call', 'calls', 'will call', 'called'], 1, 'She → calls (mệnh đề if).', $d);
        $this->quiz($L, 'Which is NOT a type 1 conditional?', ['If you try, you will win.', 'If she comes, we will eat.', 'If I were rich, I would travel.', 'If he studies, he will pass.'], 2, '"If I were rich, I would travel." là loại 2.', $d);
        $this->matching($L, 'Match to complete the type 1 sentences (set 2).', [['If you save money,', 'you will buy it.'], ['If they arrive,', 'we will begin.'], ['If she calls,', 'answer it.'], ['If he is tired,', 'let him rest.']], 'If + hiện tại đơn, will + V (hoặc mệnh lệnh).', $d);
        $this->matching($L, 'Match each situation with the right advice (set 2).', [['You are hungry.', 'If you are hungry, eat something.'], ['It is raining.', 'If it rains, take an umbrella.'], ['You are tired.', 'If you are tired, go to bed.'], ['The room is dark.', 'If it is dark, turn on the light.']], 'Lời khuyên dạng câu điều kiện loại 1.', $d);
        $this->matching($L, 'Match each error with its correction (set 2).', [['If you will come, I am happy.', 'If you come, I will be happy.'], ['If she will study, she passes.', 'If she studies, she will pass.'], ['If they comes, we will waits.', 'If they come, we will wait.'], ['If he will call, tell me.', 'If he calls, tell me.']], 'Mệnh đề if không dùng will.', $d);
        $this->matching($L, 'Match each Vietnamese meaning with the English sentence (set 2).', [['Nếu bạn cố gắng, bạn sẽ thành công.', 'If you try, you will succeed.'], ['Nếu trời mưa, chúng ta sẽ ở nhà.', 'If it rains, we will stay home.'], ['Nếu cô ấy đến, hãy báo tôi.', 'If she comes, tell me.'], ['Nếu anh ấy xin lỗi, cô ấy sẽ tha thứ.', 'If he apologizes, she will forgive him.']], 'Đọc kỹ cả hai mệnh đề để nối đúng.', $d);
        $this->matching($L, 'Match each half to complete the sentence (conditional).', [['If the weather is good,', 'we will go out.'], ['If you need help,', 'ask me.'], ['If she is busy,', 'we will wait.'], ['If he finishes,', 'we will leave.']], 'If + hiện tại đơn, will/mệnh lệnh.', $d);
        $this->sortQ($L, 'Drag each sentence into TYPE 1 or TYPE 0 (set 2).', [['If you press it, it will ring.', 'TYPE 1'], ['If you mix them, you will get purple.', 'TYPE 1'], ['If you press it, it rings.', 'TYPE 0'], ['If you mix red and blue, you get purple.', 'TYPE 0']], 'Loại 1 có will; loại 0 không có will.', $d);
        $this->sortQ($L, 'Drag each sentence into WITH COMMA or WITHOUT COMMA (set 2).', [['If you go, I will follow.', 'WITH COMMA'], ['If she calls, tell me.', 'WITH COMMA'], ['I will follow if you go.', 'WITHOUT COMMA'], ['Tell me if she calls.', 'WITHOUT COMMA']], 'Mệnh đề if đứng đầu → có dấu phẩy.', $d);
        $this->sortQ($L, 'Drag each sentence into CORRECT or INCORRECT (set 2).', [['If it stops, we will go.', 'CORRECT'], ['If they invite us, we will join.', 'CORRECT'], ['If she will come, we eat.', 'INCORRECT'], ['If you hurry, you catch.', 'INCORRECT']], 'Mệnh đề chính phải có will.', $d);
        $this->sortQ($L, 'Drag each verb into IF-CLAUSE FORM or MAIN-CLAUSE FORM.', [['rains', 'IF-CLAUSE FORM'], ['comes', 'IF-CLAUSE FORM'], ['will rain', 'MAIN-CLAUSE FORM'], ['will come', 'MAIN-CLAUSE FORM']], 'If-clause: hiện tại đơn; main clause: will + V.', $d);
        $this->sortQ($L, 'Drag each phrase into REAL POSSIBILITY or GENERAL TRUTH (set 2).', [['If you press it, it will ring.', 'REAL POSSIBILITY'], ['If she studies, she will pass.', 'REAL POSSIBILITY'], ['If you heat water, it boils.', 'GENERAL TRUTH'], ['If it rains, streets get wet.', 'GENERAL TRUTH']], 'Loại 1 = khả năng có thật; loại 0 = chân lý.', $d);
        $this->fill($L, 'If we ___ (leave) now, we will arrive on time.', [[0, 'leave']], 'If + leave (hiện tại đơn).', $d);
        $this->fill($L, 'He will fail if he ___ (not/study).', [[0, 'does not study']], 'He → does not study.', $d);
        $this->fill($L, 'If she ___ (be) sick, she will stay home.', [[0, 'is']], 'She → is.', $d);
        $this->fill($L, 'They will win if they ___ (try) hard.', [[0, 'try']], 'They → try.', $d);
        $this->fill($L, 'If you ___ (see) him, give him this letter.', [[0, 'see']], 'You → see.', $d);
    }

    // 23. tieng-anh-thpt-11-cau-bi-dong-1 (g11, trung_binh) - Bị động cơ bản
    private function seedEnPassive1(): void
    {
        $L = 'tieng-anh-thpt-11-cau-bi-dong-1'; $d = 'trung_binh';
        $this->quiz($L, 'Choose the passive: They grow rice here. (Họ trồng lúa ở đây.)', ['Rice is grown here.', 'Rice grows here.', 'Rice is growing here.', 'Rice was grown here.'], 0, 'Hiện tại đơn bị động: is + grown.', $d);
        $this->quiz($L, 'Choose the passive: She broke the vase. (Cô ấy đã làm vỡ bình hoa.)', ['The vase is broken.', 'The vase was broken.', 'The vase breaks.', 'The vase has broken.'], 1, 'Quá khứ đơn bị động: was + broken.', $d);
        $this->quiz($L, 'Choose the passive: He will finish the work. (Anh ấy sẽ hoàn thành công việc.)', ['The work is finished.', 'The work was finished.', 'The work will be finished.', 'The work finishes.'], 2, 'Tương lai đơn bị động: will be + finished.', $d);
        $this->quiz($L, 'Choose the passive: They are painting the house. (Họ đang sơn nhà.)', ['The house is painted.', 'The house was painted.', 'The house has been painted.', 'The house is being painted.'], 3, 'Tiếp diễn bị động: is being + painted.', $d);
        $this->quiz($L, 'Which sentence is passive?', ['She cleans the room.', 'The room is cleaned daily.', 'He is cleaning now.', 'They clean every day.'], 1, 'Câu bị động có be + V3: is cleaned.', $d);
        $this->matching($L, 'Match each active sentence with its passive form (set 2).', [['She writes poems.', 'Poems are written by her.'], ['He fixed the car.', 'The car was fixed by him.'], ['They will open a shop.', 'A shop will be opened.'], ['We clean the room.', 'The room is cleaned by us.']], 'Tân ngữ lên làm chủ ngữ + be + V3.', $d);
        $this->matching($L, 'Match each tense with its passive form (set 2).', [['present simple', 'am/is/are + V3'], ['past simple', 'was/were + V3'], ['future simple', 'will be + V3'], ['present continuous', 'am/is/are being + V3']], 'Công thức bị động theo thì.', $d);
        $this->matching($L, 'Match each verb with its past participle (set 2).', [['write', 'written'], ['break', 'broken'], ['speak', 'spoken'], ['choose', 'chosen']], 'write→written, break→broken, speak→spoken, choose→chosen.', $d);
        $this->matching($L, 'Match each sentence with the doer (set 2).', [['The cake was baked by Mom.', 'Mom'], ['The song was sung by her.', 'her'], ['The bridge was built in 2000.', 'unknown'], ['Rice is grown by farmers.', 'farmers']], 'Tác nhân đứng sau "by"; không có "by" thì không rõ.', $d);
        $this->matching($L, 'Match each passive with its active form.', [['The room is cleaned.', 'Someone cleans the room.'], ['The letter was sent.', 'Someone sent the letter.'], ['The cake will be eaten.', 'Someone will eat the cake.'], ['The car is being washed.', 'Someone is washing the car.']], 'Đảo ngược: chủ ngữ bị động → tân ngữ chủ động.', $d);
        $this->sortQ($L, 'Drag each sentence into ACTIVE or PASSIVE (set 2).', [['She opened the door.', 'ACTIVE'], ['He wrote a letter.', 'ACTIVE'], ['The door was opened.', 'PASSIVE'], ['A letter was written.', 'PASSIVE']], 'Bị động có be + V3.', $d);
        $this->sortQ($L, 'Drag each passive form into the correct TENSE (set 2).', [['is done', 'PRESENT'], ['was done', 'PAST'], ['will be done', 'FUTURE'], ['is being done', 'CONTINUOUS']], 'is→hiện tại, was→quá khứ, will be→tương lai, being→tiếp diễn.', $d);
        $this->sortQ($L, 'Drag each sentence into CORRECT or INCORRECT passive (set 2).', [['The cake is baked.', 'CORRECT'], ['Rice is grown here.', 'CORRECT'], ['The room was clean.', 'INCORRECT'], ['He was arrest.', 'INCORRECT']], 'Phải là was cleaned / was arrested (V3).', $d);
        $this->sortQ($L, 'Drag each verb into REGULAR or IRREGULAR participle (set 2).', [['cleaned', 'REGULAR'], ['painted', 'REGULAR'], ['written', 'IRREGULAR'], ['broken', 'IRREGULAR']], 'cleaned/painted có quy tắc; written/broken bất quy tắc.', $d);
        $this->sortQ($L, 'Drag each sentence into WITH "BY" or WITHOUT "BY".', [['It was built by him.', 'WITH "BY"'], ['She was helped by me.', 'WITH "BY"'], ['It was built in 1990.', 'WITHOUT "BY"'], ['Rice is grown here.', 'WITHOUT "BY"']], 'Tác nhân đứng sau "by".', $d);
        $this->fill($L, 'English ___ (speak) in many countries.', [[0, 'is spoken']], 'Hiện tại đơn bị động: is spoken.', $d);
        $this->fill($L, 'The house ___ (paint) last year.', [[0, 'was painted']], 'Quá khứ đơn bị động: was painted.', $d);
        $this->fill($L, 'The reports ___ (finish) tomorrow.', [[0, 'will be finished']], 'Tương lai đơn bị động: will be finished.', $d);
        $this->fill($L, 'The streets ___ (clean) every morning.', [[0, 'are cleaned']], 'The streets (số nhiều) → are cleaned.', $d);
        $this->fill($L, 'A new bridge ___ (build) over the river now.', [[0, 'is being built']], 'Tiếp diễn bị động: is being built.', $d);
    }

    // 24. tieng-anh-thpt-11-cau-bi-dong-2 (g11, kho) - Bị động nâng cao
    private function seedEnPassive2(): void
    {
        $L = 'tieng-anh-thpt-11-cau-bi-dong-2'; $d = 'kho';
        $this->quiz($L, 'Choose the passive: They should do it now. (Họ nên làm ngay bây giờ.)', ['It should be done now.', 'It should do now.', 'It is should done.', 'It should been done.'], 0, 'Modal bị động: should be + done.', $d);
        $this->quiz($L, 'Choose the passive: She has finished the report. (Cô ấy đã hoàn thành báo cáo.)', ['The report has finished.', 'The report has been finished.', 'The report was finished.', 'The report is finished.'], 1, 'Hoàn thành bị động: has been + finished.', $d);
        $this->quiz($L, 'Choose the passive: We must obey the rules. (Chúng ta phải tuân thủ nội quy.)', ['The rules must obey.', 'The rules must be obey.', 'The rules must be obeyed.', 'The rules must obeyed.'], 2, 'must be + V3: must be obeyed.', $d);
        $this->quiz($L, 'Choose the passive: They are going to build a mall. (Họ sẽ xây trung tâm thương mại.)', ['A mall is going to build.', 'A mall is going to be build.', 'A mall is going to built.', 'A mall is going to be built.'], 3, 'be going to be + V3.', $d);
        $this->quiz($L, 'Which sentence is correct?', ['The room must be cleaned.', 'The room must clean.', 'The room must cleaned.', 'The room must to be cleaned.'], 0, 'must be + V3: must be cleaned.', $d);
        $this->matching($L, 'Match each modal active with its passive (set 2).', [['We can solve it.', 'It can be solved.'], ['You must do it.', 'It must be done.'], ['They should help him.', 'He should be helped.'], ['She may open it.', 'It may be opened.']], 'Modal bị động: modal + be + V3.', $d);
        $this->matching($L, 'Match each tense with its passive form (advanced, set 2).', [['present perfect', 'has/have been + V3'], ['past perfect', 'had been + V3'], ['modal', 'modal + be + V3'], ['be going to', 'be going to be + V3']], 'Công thức bị động nâng cao.', $d);
        $this->matching($L, 'Match each verb with its past participle (set 2).', [['teach', 'taught'], ['buy', 'bought'], ['send', 'sent'], ['think', 'thought']], 'teach→taught, buy→bought, send→sent, think→thought.', $d);
        $this->matching($L, 'Match each double-object verb with its two objects.', [['She gave me a book.', 'me / a book'], ['He told us a story.', 'us / a story'], ['They offered her a job.', 'her / a job'], ['We sent them a letter.', 'them / a letter']], 'Động từ hai tân ngữ: người + vật.', $d);
        $this->matching($L, 'Match each active with its passive (perfect).', [['They have eaten it.', 'It has been eaten.'], ['She had done it.', 'It had been done.'], ['We have seen him.', 'He has been seen.'], ['He had broken it.', 'It had been broken.']], 'Hoàn thành bị động: have/had been + V3.', $d);
        $this->sortQ($L, 'Drag each passive into MODAL or PERFECT (set 2).', [['must be done', 'MODAL'], ['can be seen', 'MODAL'], ['has been done', 'PERFECT'], ['had been written', 'PERFECT']], 'Modal + be + V3; have/had been + V3.', $d);
        $this->sortQ($L, 'Drag each sentence into CORRECT or INCORRECT (set 2).', [['She has been seen.', 'CORRECT'], ['He had been helped.', 'CORRECT'], ['It must be do.', 'INCORRECT'], ['They should be tell.', 'INCORRECT']], 'Phải là must be done / should be told.', $d);
        $this->sortQ($L, 'Drag each passive into WITH AGENT or WITHOUT AGENT (set 2).', [['was written by Nam', 'WITH AGENT'], ['was painted by her', 'WITH AGENT'], ['was written in 2020', 'WITHOUT AGENT'], ['was built last year', 'WITHOUT AGENT']], 'Tác nhân đi sau "by".', $d);
        $this->sortQ($L, 'Drag each structure into PASSIVE or NOT PASSIVE (set 2).', [['be + V3', 'PASSIVE'], ['modal + be + V3', 'PASSIVE'], ['be + V-ing', 'NOT PASSIVE'], ['have + V3', 'NOT PASSIVE']], 'Bị động luôn có be + V3.', $d);
        $this->sortQ($L, 'Drag each sentence into DOUBLE OBJECT or SINGLE OBJECT.', [['She gave me a gift.', 'DOUBLE OBJECT'], ['He told us a lie.', 'DOUBLE OBJECT'], ['She bought a gift.', 'SINGLE OBJECT'], ['He told a lie.', 'SINGLE OBJECT']], 'Hai tân ngữ: người + vật.', $d);
        $this->fill($L, 'The cake can ___ (cut) into six pieces.', [[0, 'be cut']], 'can be + V3: be cut.', $d);
        $this->fill($L, 'The project has ___ (complete).', [[0, 'been completed']], 'has been + completed.', $d);
        $this->fill($L, 'He was ___ (tell) the news yesterday.', [[0, 'told']], 'was told (V3 của tell).', $d);
        $this->fill($L, 'The old house is going to ___ (pull) down.', [[0, 'be pulled']], 'be going to be + pulled.', $d);
        $this->fill($L, 'Smoking must not ___ (allow) here.', [[0, 'be allowed']], 'must not be + allowed.', $d);
    }

    // 25. tieng-anh-thpt-11-cau-tuong-thuat-1 (g11, trung_binh) - Tường thuật (1)
    private function seedEnReported1(): void
    {
        $L = 'tieng-anh-thpt-11-cau-tuong-thuat-1'; $d = 'trung_binh';
        $this->quiz($L, 'Choose the reported speech: He said, "I like tea." (Anh ấy nói: "Tôi thích trà.")', ['He said he likes tea.', 'He said he liked tea.', 'He said he like tea.', 'He said he had liked tea.'], 1, 'Lùi thì: like → liked.', $d);
        $this->quiz($L, 'Choose the reported speech: She said, "We are busy." (Cô ấy nói: "Chúng tôi bận.")', ['She said they are busy.', 'She said they were busy.', 'She said we were busy.', 'She said they had busy.'], 1, 'we → they; are → were.', $d);
        $this->quiz($L, 'Choose the reported question: He asked, "Do you swim?" (Anh ấy hỏi: "Bạn có bơi không?")', ['He asked do I swim.', 'He asked if I swam.', 'He asked if I swim.', 'He asked that I swam.'], 1, 'Câu hỏi Yes/No → if + lùi thì.', $d);
        $this->quiz($L, 'Choose the reported question: She asked, "What is this?" (Cô ấy hỏi: "Đây là gì?")', ['She asked what is this.', 'She asked what this was.', 'She asked what was this.', 'She asked that this was.'], 1, 'Câu hỏi Wh- → what + S + V (lùi thì), không đảo.', $d);
        $this->quiz($L, 'Choose the correct backshift: "I can swim" → She said she ___ swim.', ['can', 'could', 'will', 'would'], 1, 'can → could.', $d);
        $this->matching($L, 'Match each direct speech with its reported form (set 2).', [['"I am late," he said.', 'He said he was late.'], ['"We won," they said.', 'They said they had won.'], ['"I will go," she said.', 'She said she would go.'], ['"I can do it," he said.', 'He said he could do it.']], 'am→was, won→had won, will→would, can→could.', $d);
        $this->matching($L, 'Match each tense with its backshift (set 2).', [['present simple', 'past simple'], ['present continuous', 'past continuous'], ['past simple', 'past perfect'], ['will', 'would']], 'Quy tắc lùi thì.', $d);
        $this->matching($L, 'Match each time word with its reported form (set 2).', [['today', 'that day'], ['tomorrow', 'the next day'], ['yesterday', 'the day before'], ['now', 'then']], 'today→that day, tomorrow→the next day, yesterday→the day before, now→then.', $d);
        $this->matching($L, 'Match each question with its reported form (set 2).', [['"Are you OK?" she asked.', 'She asked if I was OK.'], ['"Where is he?" she asked.', 'She asked where he was.'], ['"Do you like it?" he asked.', 'He asked if I liked it.'], ['"Why did you go?" he asked.', 'He asked why I had gone.']], 'Yes/No → if; Wh- → giữ từ để hỏi; lùi thì.', $d);
        $this->matching($L, 'Match each direct sentence with its reported form (set 3).', [['"My dad is here," she said.', 'She said her dad was there.'], ['"We like this," they said.', 'They said they liked that.'], ['"I saw you," he said.', 'He said he had seen me.'], ['"You are kind," she said.', 'She said I was kind.']], 'Đổi đại từ, trạng từ và lùi thì.', $d);
        $this->sortQ($L, 'Drag each pair into CORRECT or INCORRECT backshift (set 2).', [['am → was', 'CORRECT'], ['will → would', 'CORRECT'], ['can → can', 'INCORRECT'], ['have → have', 'INCORRECT']], 'can→could; have→had.', $d);
        $this->sortQ($L, 'Drag each reported sentence into STATEMENT or QUESTION (set 2).', [['She said she was tired.', 'STATEMENT'], ['He said he would come.', 'STATEMENT'], ['She asked if I was tired.', 'QUESTION'], ['He asked where I lived.', 'QUESTION']], 'said → trần thuật; asked → câu hỏi.', $d);
        $this->sortQ($L, 'Drag each change into NEEDED or NOT NEEDED (set 2).', [['I → he', 'NEEDED'], ['yesterday → the day before', 'NEEDED'], ['yesterday → yesterday', 'NOT NEEDED'], ['the → the', 'NOT NEEDED']], 'Đại từ và trạng từ thời gian cần đổi.', $d);
        $this->sortQ($L, 'Drag each verb into SAID or TOLD pattern (set 2).', [['She said that...', 'SAID'], ['He said he would go.', 'SAID'], ['She told me that...', 'TOLD'], ['He told us he would go.', 'TOLD']], 'said (không tân ngữ) / told + tân ngữ.', $d);
        $this->sortQ($L, 'Drag each sentence into DIRECT or REPORTED.', [['"I am happy," she said.', 'DIRECT'], ['"We will win," he said.', 'DIRECT'], ['She said she was happy.', 'REPORTED'], ['He said they would win.', 'REPORTED']], 'Trực tiếp có dấu ngoặc kép; tường thuật lùi thì.', $d);
        $this->fill($L, 'He said, "I am busy." → He said he ___ busy.', [[0, 'was']], 'am → was.', $d);
        $this->fill($L, '"We will win," they said. → They said they ___ win.', [[0, 'would']], 'will → would.', $d);
        $this->fill($L, 'She asked, "Do you like coffee?" → She asked ___ I liked coffee.', [[0, 'if']], 'Câu hỏi Yes/No → if.', $d);
        $this->fill($L, 'He asked, "What do you want?" → He asked what I ___.', [[0, 'wanted']], 'do want → wanted (lùi thì).', $d);
        $this->fill($L, '"I can help," she said. → She said she ___ help.', [[0, 'could']], 'can → could.', $d);
    }

    // 26. tieng-anh-thpt-11-cau-tuong-thuat-2 (g11, kho) - Tường thuật (2)
    private function seedEnReported2(): void
    {
        $L = 'tieng-anh-thpt-11-cau-tuong-thuat-2'; $d = 'kho';
        $this->quiz($L, 'Choose the reported speech: He said, "Open the window!" (Anh ấy nói: "Mở cửa sổ ra!")', ['He told me open the window.', 'He told me to open the window.', 'He said me to open the window.', 'He asked open the window.'], 1, 'Mệnh lệnh → told + O + to V.', $d);
        $this->quiz($L, 'Choose the reported speech: She said, "Don\'t be late!" (Cô ấy nói: "Đừng đến muộn!")', ['She told me don\'t be late.', 'She told me not to be late.', 'She said not be late.', 'She asked me be late.'], 1, 'Phủ định: told + O + not to V.', $d);
        $this->quiz($L, 'Choose the reported speech: He said, "If I had time, I would go." (Câu điều kiện loại 3)', ['He said if he had time, he would go.', 'He said if he has time, he will go.', 'He said if he had had time, he would have gone.', 'He said if he would have time, he goes.'], 0, 'Câu điều kiện loại 2/3 giữ nguyên.', $d);
        $this->quiz($L, 'Choose the reported request: "Don\'t touch!" he said. (Anh ấy nói: "Đừng chạm vào!")', ['He told me don\'t touch.', 'He told me not to touch.', 'He said me not touch.', 'He asked not to touch me.'], 1, 'told + O + not to V.', $d);
        $this->quiz($L, 'Choose the correct reporting verb: She ___ me to sit down. (Cô ấy bảo tôi ngồi xuống.)', ['said', 'told', 'spoke', 'talked'], 1, 'told + tân ngữ + to V.', $d);
        $this->matching($L, 'Match each command with its reported form (set 2).', [['"Sit down," she said.', 'She told me to sit down.'], ['"Don\'t run," he said.', 'He told me not to run.'], ['"Be quiet," she said.', 'She told us to be quiet.'], ['"Hurry up," he said.', 'He told me to hurry up.']], 'Mệnh lệnh → told + O + (not) to V.', $d);
        $this->matching($L, 'Match each reporting verb with its pattern (set 2).', [['advise', 'advise sb to do'], ['order', 'order sb to do'], ['warn', 'warn sb not to do'], ['invite', 'invite sb to do']], 'advise/order/invite + sb + to V; warn + sb + not to V.', $d);
        $this->matching($L, 'Match each conditional with its reported form (set 2).', [['"If I were you, I would go," she said.', 'She said if she were me, she would go.'], ['"If it rains, we will stay," he said.', 'He said if it rained, they would stay.'], ['"If you had asked, I would help," she said.', 'She said if I had asked, she would have helped.'], ['"If I see him, I will tell," he said.', 'He said if he saw him, he would tell.']], 'Loại 1 lùi thì; loại 2/3 giữ nguyên.', $d);
        $this->matching($L, 'Match each sentence with its reported form (modal).', [['"You must go," she said.', 'She said I had to go.'], ['"We should leave," he said.', 'He said they should leave.'], ['"I may come," she said.', 'She said she might come.'], ['"You needn\'t wait," he said.', 'He said I didn\'t need to wait.']], 'must→had to, may→might, needn\'t→didn\'t need to; should giữ nguyên.', $d);
        $this->matching($L, 'Match each request with its reported form.', [['"Please help me," she said.', 'She asked me to help her.'], ['"Could you open it?" he said.', 'He asked me to open it.'], ['"Please don\'t go," she said.', 'She asked me not to go.'], ['"Would you wait?" he said.', 'He asked me to wait.']], 'Lời yêu cầu → asked + O + (not) to V.', $d);
        $this->sortQ($L, 'Drag each reported form into COMMAND or STATEMENT (set 2).', [['She told me to go.', 'COMMAND'], ['He ordered us to stop.', 'COMMAND'], ['She said she was ill.', 'STATEMENT'], ['He said he was tired.', 'STATEMENT']], 'told/ordered + to V = mệnh lệnh; said = trần thuật.', $d);
        $this->sortQ($L, 'Drag each sentence into CORRECT or INCORRECT reported command (set 2).', [['She told me to sit.', 'CORRECT'], ['He told me not to go.', 'CORRECT'], ['She said me to sit.', 'INCORRECT'], ['He asked me go.', 'INCORRECT']], 'said không có tân ngữ; asked + to V.', $d);
        $this->sortQ($L, 'Drag each conditional into BACKSHIFT or KEEP THE SAME (set 2).', [['If I see him, I will tell.', 'BACKSHIFT'], ['If it rains, we stay.', 'BACKSHIFT'], ['If I were rich, I would travel.', 'KEEP THE SAME'], ['If he had come, we would win.', 'KEEP THE SAME']], 'Loại 1 lùi thì; loại 2/3 giữ nguyên.', $d);
        $this->sortQ($L, 'Drag each reporting verb into CORRECT PATTERN or WRONG (set 2).', [['told me to go', 'CORRECT PATTERN'], ['asked me to wait', 'CORRECT PATTERN'], ['said me to go', 'WRONG'], ['ordered me go', 'WRONG']], 'said không đi với tân ngữ; order + to V.', $d);
        $this->sortQ($L, 'Drag each sentence into REQUEST or ORDER.', [['She asked me to help.', 'REQUEST'], ['He asked us to wait.', 'REQUEST'], ['He ordered me to stop.', 'ORDER'], ['She told him to leave.', 'ORDER']], 'asked = yêu cầu; ordered/told (ra lệnh) = mệnh lệnh.', $d);
        $this->fill($L, '"Sit down," he said. → He told me ___ sit down.', [[0, 'to']], 'told + O + to V.', $d);
        $this->fill($L, '"Don\'t laugh," she said. → She told us ___ to laugh.', [[0, 'not']], 'told + O + not to V.', $d);
        $this->fill($L, '"Please lend me your pen," he said. → He ___ me to lend him my pen.', [[0, 'asked']], 'Lời yêu cầu → asked.', $d);
        $this->fill($L, 'Type 3 conditional in reported speech ___ the same. (keep/change)', [[0, 'keeps']], 'Loại 3 giữ nguyên (keeps).', $d);
        $this->fill($L, '"You should rest," she said. → She ___ me to rest.', [[0, 'advised']], 'Lời khuyên → advised + O + to V.', $d);
    }

    // 27. tieng-anh-thpt-12-menh-de-quan-he-1 (g12, kho) - Rút gọn V-ing/V-ed
    private function seedEnRelative1(): void
    {
        $L = 'tieng-anh-thpt-12-menh-de-quan-he-1'; $d = 'kho';
        $this->quiz($L, 'Choose the reduced form: The man who lives next door is a doctor. (Người đàn ông sống cạnh nhà là bác sĩ.)', ['The man lives next door is a doctor.', 'The man living next door is a doctor.', 'The man lived next door is a doctor.', 'The man to live next door is a doctor.'], 1, 'Chủ động → V-ing: living.', $d);
        $this->quiz($L, 'Choose the reduced form: The picture which was painted by Nam is beautiful. (Bức tranh do Nam vẽ rất đẹp.)', ['The picture painted by Nam is beautiful.', 'The picture painting by Nam is beautiful.', 'The picture was painted by Nam is beautiful.', 'The picture to paint by Nam is beautiful.'], 0, 'Bị động → V-ed: painted.', $d);
        $this->quiz($L, 'Choose the reduced form: The students who are playing outside are noisy. (Học sinh đang chơi bên ngoài rất ồn.)', ['The students played outside are noisy.', 'The students to play outside are noisy.', 'The students playing outside are noisy.', 'The students play outside are noisy.'], 2, 'Chủ động → V-ing: playing.', $d);
        $this->quiz($L, 'When do we use V-ed to reduce a relative clause?', ['active voice', 'continuous action', 'after "the first"', 'passive meaning'], 3, 'V-ed dùng khi mệnh đề mang nghĩa bị động.', $d);
        $this->quiz($L, 'Choose the correct sentence:', ['The letters written yesterday are mine.', 'The letters writing yesterday are mine.', 'The letters wrote yesterday are mine.', 'The letters to write yesterday are mine.'], 0, 'Bị động → written.', $d);
        $this->matching($L, 'Match each relative clause with its reduced form (set 2).', [['The girl who is dancing is Mai.', 'The girl dancing is Mai.'], ['The book which was lost is mine.', 'The book lost is mine.'], ['The man who works here is kind.', 'The man working here is kind.'], ['The car which was stolen is old.', 'The car stolen is old.']], 'Chủ động → V-ing; bị động → V-ed.', $d);
        $this->matching($L, 'Match each sentence with its reduction type (set 2).', [['The boy playing is my son.', 'V-ing'], ['The task finished is mine.', 'V-ed'], ['The dog barking is loud.', 'V-ing'], ['The window broken is old.', 'V-ed']], 'playing/barking = V-ing; finished/broken = V-ed.', $d);
        $this->matching($L, 'Match each sentence with its full form.', [['The man standing there is Tom.', 'The man who is standing there is Tom.'], ['The report written today is long.', 'The report which was written today is long.'], ['The girl singing is happy.', 'The girl who is singing is happy.'], ['The bike stolen was red.', 'The bike which was stolen was red.']], 'Dạng đầy đủ có who/which + be.', $d);
        $this->matching($L, 'Match each clause with its reduced form (set 2).', [['who is waiting', 'waiting'], ['which was built', 'built'], ['who are talking', 'talking'], ['which were made', 'made']], 'who is/are + V-ing → V-ing; which was/were + V3 → V-ed.', $d);
        $this->matching($L, 'Match each error with its correction (reduction).', [['The man stand there is Tom.', 'The man standing there is Tom.'], ['The book was written is mine.', 'The book written is mine.'], ['The girl dance is Mai.', 'The girl dancing is Mai.'], ['The car was stolen is old.', 'The car stolen is old.']], 'Bỏ who is/was, giữ lại V-ing/V-ed.', $d);
        $this->sortQ($L, 'Drag each reduced clause into V-ING (active) or V-ED (passive) (set 2).', [['the boy running', 'V-ING (active)'], ['the dog barking', 'V-ING (active)'], ['the letter written', 'V-ED (passive)'], ['the window broken', 'V-ED (passive)']], 'Chủ động → V-ing; bị động → V-ed.', $d);
        $this->sortQ($L, 'Drag each sentence into CORRECT or INCORRECT reduction (set 2).', [['The girl singing is Mai.', 'CORRECT'], ['The task done is yours.', 'CORRECT'], ['The man stands there is Tom.', 'INCORRECT'], ['The book was lost is mine.', 'INCORRECT']], 'Phải là standing / lost (bỏ was).', $d);
        $this->sortQ($L, 'Drag each clause into REDUCIBLE or NOT REDUCIBLE (set 2).', [['who is playing', 'REDUCIBLE'], ['which was built', 'REDUCIBLE'], ['whose bike is red', 'NOT REDUCIBLE'], ['whom I met', 'NOT REDUCIBLE']], 'whose/whom không rút gọn bằng V-ing/V-ed.', $d);
        $this->sortQ($L, 'Drag each noun phrase into ACTIVE MEANING or PASSIVE MEANING (set 2).', [['the boy crying', 'ACTIVE MEANING'], ['the dog barking', 'ACTIVE MEANING'], ['the boy punished', 'PASSIVE MEANING'], ['the car stolen', 'PASSIVE MEANING']], 'crying/barking = chủ động; punished/stolen = bị động.', $d);
        $this->sortQ($L, 'Drag each sentence into FULL CLAUSE or REDUCED CLAUSE.', [['The man who is standing is Tom.', 'FULL CLAUSE'], ['The book which was lost is mine.', 'FULL CLAUSE'], ['The man standing is Tom.', 'REDUCED CLAUSE'], ['The book lost is mine.', 'REDUCED CLAUSE']], 'Rút gọn bỏ đại từ quan hệ + be.', $d);
        $this->fill($L, 'The woman who is waiting is my aunt. → The woman ___ is my aunt.', [[0, 'waiting']], 'Chủ động → waiting.', $d);
        $this->fill($L, 'The letter which was sent yesterday arrived. → The letter ___ yesterday arrived.', [[0, 'sent']], 'Bị động → sent.', $d);
        $this->fill($L, 'People who smoke harm their health. → People ___ harm their health.', [[0, 'smoking']], 'Chủ động → smoking.', $d);
        $this->fill($L, 'The thief who was caught ran away. → The thief ___ ran away.', [[0, 'caught']], 'Bị động → caught.', $d);
        $this->fill($L, 'We reduce "who is + V-ing" to ___.', [[0, 'V-ing']], 'Chủ động rút gọn thành V-ing.', $d);
    }

    // 28. tieng-anh-thpt-12-menh-de-quan-he-2 (g12, kho) - Rút gọn to-V
    private function seedEnRelative2(): void
    {
        $L = 'tieng-anh-thpt-12-menh-de-quan-he-2'; $d = 'kho';
        $this->quiz($L, 'Choose the reduced form: He was the last person to leave the room. (Anh ấy là người cuối cùng rời phòng.)', ['He was the last person left the room.', 'He was the last person leaving the room.', 'He was the last person to leave the room.', 'He was the last person leave the room.'], 2, 'Sau "the last" dùng to-V.', $d);
        $this->quiz($L, 'Choose the correct reduction: She is the only student to get full marks. (full: ...who got...)', ['She is the only student getting full marks.', 'She is the only student got full marks.', 'She is the only student to get full marks.', 'She is the only student get full marks.'], 2, 'Sau "the only" dùng to-V.', $d);
        $this->quiz($L, 'Which reduction is correct? Yuri Gagarin was the first man ___ into space.', ['flying', 'flew', 'to fly', 'flown'], 2, 'Sau "the first" dùng to-V: to fly.', $d);
        $this->quiz($L, 'Choose the correct sentence:', ['She was the first to arrive.', 'She was the first arriving.', 'She was the first arrived.', 'She was the first arrive.'], 0, 'the first + to-V.', $d);
        $this->quiz($L, 'After which words do we use to-V reduction?', ['who, which, that', 'the first, the last, the only', 'a, an, the', 'this, that, these'], 1, 'Sau the first/the last/the only/only dùng to-V.', $d);
        $this->matching($L, 'Match each full clause with its to-V reduction (set 2).', [['the first person who came', 'the first person to come'], ['the last bus which leaves', 'the last bus to leave'], ['the only way which works', 'the only way to work'], ['the best time which suits', 'the best time to suit']], 'the first/last/only/best + to-V.', $d);
        $this->matching($L, 'Match each sentence with the reduction type (set 2).', [['She was the first to speak.', 'to-V'], ['The boy running is Tom.', 'V-ing'], ['The task done is mine.', 'V-ed'], ['He is the last to go.', 'to-V']], 'the first/last → to-V; chủ động → V-ing; bị động → V-ed.', $d);
        $this->matching($L, 'Match each error with its correction (set 2).', [['She was the first arriving.', 'She was the first to arrive.'], ['He is the only got full marks.', 'He is the only to get full marks.'], ['The house build next year is big.', 'The house to be built next year is big.'], ['They were the last leaving.', 'They were the last to leave.']], 'the first/only/last + to-V; bị động tương lai: to be built.', $d);
        $this->matching($L, 'Match each situation with the correct reduction (set 2).', [['the first student', 'to-V'], ['the boy playing', 'V-ing'], ['the letter written', 'V-ed'], ['the only solution', 'to-V']], 'the first/only → to-V; playing → V-ing; written → V-ed.', $d);
        $this->matching($L, 'Match each sentence with its full form (set 2).', [['The first to come was Mai.', 'The first who came was Mai.'], ['The last to leave was Tom.', 'The last who left was Tom.'], ['The only to pass was Nam.', 'The only who passed was Nam.'], ['The best to win was Hoa.', 'The best who won was Hoa.']], 'Dạng đầy đủ có who + động từ.', $d);
        $this->sortQ($L, 'Drag each phrase into TO-V or V-ING/V-ED reduction (set 2).', [['the first to go', 'TO-V'], ['the only to know', 'TO-V'], ['the boy singing', 'V-ING/V-ED'], ['the work done', 'V-ING/V-ED']], 'the first/only → to-V; singing → V-ing; done → V-ed.', $d);
        $this->sortQ($L, 'Drag each sentence into CORRECT or INCORRECT (set 2).', [['She was the first to finish.', 'CORRECT'], ['They were the only to stay.', 'CORRECT'], ['He was the last finished.', 'INCORRECT'], ['It was the best win.', 'INCORRECT']], 'Phải là the last to finish / the best to win.', $d);
        $this->sortQ($L, 'Drag each clause into ACTIVE (V-ing/to-V) or PASSIVE (V-ed/to be V-ed) (set 2).', [['the man waiting', 'ACTIVE'], ['the first to come', 'ACTIVE'], ['the report written', 'PASSIVE'], ['the bridge to be built', 'PASSIVE']], 'waiting/to come = chủ động; written/to be built = bị động.', $d);
        $this->sortQ($L, 'Drag each phrase into CAN REDUCE WITH TO-V or CANNOT (set 2).', [['the first man', 'CAN REDUCE WITH TO-V'], ['the only way', 'CAN REDUCE WITH TO-V'], ['the man', 'CANNOT'], ['a nice day', 'CANNOT']], 'Chỉ sau the first/last/only/best mới rút gọn to-V.', $d);
        $this->sortQ($L, 'Drag each sentence into SUPERLATIVE TRIGGER or NOT.', [['the best player to join', 'SUPERLATIVE TRIGGER'], ['the first guest to arrive', 'SUPERLATIVE TRIGGER'], ['a good player', 'NOT'], ['the tall man', 'NOT']], 'the best/the first kích hoạt rút gọn to-V.', $d);
        $this->fill($L, 'He was the second person ___ (leave) the hall.', [[0, 'to leave']], 'Số thứ tự + to-V: to leave.', $d);
        $this->fill($L, 'She is the only one ___ (know) the answer.', [[0, 'to know']], 'the only + to-V: to know.', $d);
        $this->fill($L, 'The house ___ (build) in 2025 will be sold.', [[0, 'to be built']], 'Bị động tương lai: to be built.', $d);
        $this->fill($L, 'After "the best", reduce with ___.', [[0, 'to-V']], 'Sau "the best" dùng to-V.', $d);
        $this->fill($L, 'Neil Armstrong was the first man ___ (walk) on the moon.', [[0, 'to walk']], 'the first + to-V: to walk.', $d);
    }

    // 29. tieng-anh-thpt-12-dao-ngu-1 (g12, kho) - Đảo ngữ cơ bản
    private function seedEnInversion1(): void
    {
        $L = 'tieng-anh-thpt-12-dao-ngu-1'; $d = 'kho';
        $this->quiz($L, 'Choose the correct inversion: Never ___ to this place before. (Tôi chưa từng đến nơi này trước đây.)', ['have I been', 'I have been', 'had I been', 'I had been'], 0, 'Never + have + S + V3.', $d);
        $this->quiz($L, 'Choose the correct inversion: Seldom ___ such good weather. (Chúng tôi hiếm khi thấy thời tiết đẹp như vậy.)', ['we see', 'have we seen', 'we have seen', 'do we see'], 1, 'Seldom + have + S + V3.', $d);
        $this->quiz($L, 'Choose the correct inversion: Only after the rain ___ go out. (Chỉ sau cơn mưa chúng tôi mới ra ngoài.)', ['we went', 'went we', 'did we go', 'we did go'], 2, 'Only after... + did + S + V.', $d);
        $this->quiz($L, 'Choose the correct inversion: Scarcely ___ when she knocked. (Anh ấy vừa ngủ thì cô ấy gõ cửa.)', ['he slept', 'slept he', 'he had slept', 'had he slept'], 3, 'Scarcely + had + S + V3.', $d);
        $this->quiz($L, 'What happens when a negative adverb comes first? (Khi trạng từ phủ định đứng đầu câu?)', ['nothing changes', 'subject and verb invert', 'we add "not"', 'the verb disappears'], 1, 'Trạng từ phủ định đầu câu → đảo ngữ.', $d);
        $this->matching($L, 'Match each normal sentence with its inversion (set 2).', [['I have never seen it.', 'Never have I seen it.'], ['She rarely goes out.', 'Rarely does she go out.'], ['He had hardly left when it rained.', 'Hardly had he left when it rained.'], ['They seldom visit us.', 'Seldom do they visit us.']], 'Trạng từ phủ định đầu câu → đảo trợ động từ.', $d);
        $this->matching($L, 'Match each adverb with its meaning (set 2).', [['never', 'không bao giờ'], ['rarely', 'hiếm khi'], ['seldom', 'hiếm khi'], ['hardly', 'hầu như không']], 'never = không bao giờ, rarely/seldom = hiếm khi, hardly = hầu như không.', $d);
        $this->matching($L, 'Match each structure with its pattern (set 2).', [['Never + aux + S + V', 'negative adverb inversion'], ['Not only + aux + S + V', 'correlative inversion'], ['Hardly + had + S + V3', 'past perfect inversion'], ['Only when + clause + aux + S', 'only-inversion']], 'Các mẫu đảo ngữ cơ bản.', $d);
        $this->matching($L, 'Match each half to complete the inversion (set 2).', [['Never', 'have I seen'], ['Rarely', 'does she come'], ['Hardly', 'had he arrived'], ['Seldom', 'do we meet']], 'Never/hardly + have/had; rarely/seldom + do/does.', $d);
        $this->matching($L, 'Match each inversion with its normal form.', [['Never have I lied.', 'I have never lied.'], ['Rarely does he smile.', 'He rarely smiles.'], ['Little did she know.', 'She knew little.'], ['Not only was he late.', 'He was not only late.']], 'Đảo ngược lại thành câu thường.', $d);
        $this->sortQ($L, 'Drag each sentence into INVERSION or NORMAL ORDER (set 2).', [['Never did I think so.', 'INVERSION'], ['Rarely does she cry.', 'INVERSION'], ['I never thought so.', 'NORMAL ORDER'], ['She rarely cries.', 'NORMAL ORDER']], 'Đảo ngữ: trạng từ phủ định + trợ động từ + chủ ngữ.', $d);
        $this->sortQ($L, 'Drag each sentence into CORRECT or INCORRECT inversion (set 2).', [['Rarely does she come.', 'CORRECT'], ['Seldom do we agree.', 'CORRECT'], ['Never I have seen it.', 'INCORRECT'], ['Hardly he had left.', 'INCORRECT']], 'Phải đảo trợ động từ lên trước chủ ngữ.', $d);
        $this->sortQ($L, 'Drag each adverb into CAUSES INVERSION or NOT (set 2).', [['never', 'CAUSES INVERSION'], ['rarely', 'CAUSES INVERSION'], ['seldom', 'CAUSES INVERSION'], ['often', 'NOT'], ['always', 'NOT']], 'never/rarely/seldom gây đảo ngữ; often/always không.', $d);
        $this->sortQ($L, 'Drag each part into the right position: "___ ___ ___ late." (Rarely / does / she)', [['Rarely', 'POSITION 1'], ['does', 'POSITION 2'], ['she', 'POSITION 3']], 'Rarely does she...: trạng từ + trợ động từ + chủ ngữ.', $d);
        $this->sortQ($L, 'Drag each sentence into PAST or PRESENT inversion.', [['Hardly had he come.', 'PAST'], ['Never had I seen it.', 'PAST'], ['Never does she lie.', 'PRESENT'], ['Rarely do we meet.', 'PRESENT']], 'had → quá khứ; do/does → hiện tại.', $d);
        $this->fill($L, 'Seldom ___ they visit us. (do/does)', [[0, 'do']], 'They → do.', $d);
        $this->fill($L, 'Never ___ she been so happy. (has/have)', [[0, 'has']], 'She → has.', $d);
        $this->fill($L, 'Little ___ he know the truth. (did/does)', [[0, 'did']], 'Quá khứ → did.', $d);
        $this->fill($L, 'Not only ___ he sing, but he also dances. (do/does)', [[0, 'does']], 'He → does.', $d);
        $this->fill($L, 'No sooner had he come ___ she left. (than/when)', [[0, 'than']], 'No sooner... than...', $d);
    }

    // 30. tieng-anh-thpt-12-dao-ngu-2 (g12, kho) - Đảo ngữ nâng cao
    private function seedEnInversion2(): void
    {
        $L = 'tieng-anh-thpt-12-dao-ngu-2'; $d = 'kho';
        $this->quiz($L, 'Choose the inversion: If he should come, tell me. → ___', ['Should he come, tell me.', 'Should come he, tell me.', 'If should he come, tell me.', 'Were he come, tell me.'], 0, 'Bỏ if, đảo should lên đầu.', $d);
        $this->quiz($L, 'Choose the inversion: If I had wings, I would fly. → ___', ['Were I to have wings, I would fly.', 'Had I wings, I would fly.', 'Should I have wings, I would fly.', 'If had I wings, I would fly.'], 1, 'Loại 3: bỏ if, đảo had lên đầu.', $d);
        $this->quiz($L, 'Choose the inversion: If we had left earlier, we would be there. → ___', ['Were we left earlier, we would be there.', 'Should we leave earlier, we would be there.', 'Had we left earlier, we would be there.', 'If had we left earlier, we would be there.'], 2, 'Had + S + V3.', $d);
        $this->quiz($L, 'Choose the correct inversion with "only": She cries only when she is alone.', ['Only when she is alone cries she.', 'She only cries when she is alone does.', 'Only when does she is alone she cries.', 'Only when she is alone does she cry.'], 3, 'Only when... + does + S + V.', $d);
        $this->quiz($L, 'Which words replace "if" in conditional inversion?', ['should, were, had', 'do, does, did', 'never, rarely, seldom', 'not, no, never'], 0, 'should (loại 1), were (loại 2), had (loại 3).', $d);
        $this->matching($L, 'Match each conditional with its inversion (set 2).', [['If he should call, tell me.', 'Should he call, tell me.'], ['If she were free, she would come.', 'Were she free, she would come.'], ['If they had seen it, they would laugh.', 'Had they seen it, they would laugh.'], ['If I were you, I would go.', 'Were I you, I would go.']], 'Bỏ if, đảo should/were/had lên đầu.', $d);
        $this->matching($L, 'Match each "only" phrase with its inversion (set 2).', [['Only then did I understand.', 'I understood only then.'], ['Only by working hard will you succeed.', 'You will succeed only by working hard.'], ['Only after the war did he return.', 'He returned only after the war.'], ['Only when it rains do we stay.', 'We stay only when it rains.']], 'Only... đầu câu → đảo ngữ.', $d);
        $this->matching($L, 'Match each inversion with its normal form (set 2).', [['Should you need me, call.', 'If you should need me, call.'], ['Were he rich, he would help.', 'If he were rich, he would help.'], ['Had she studied, she would pass.', 'If she had studied, she would pass.'], ['Never have I seen it.', 'I have never seen it.']], 'Thêm lại "if" để được câu thường.', $d);
        $this->matching($L, 'Match each auxiliary with its conditional type.', [['should', 'type 1'], ['were', 'type 2'], ['had', 'type 3'], ['did', 'negative adverb']], 'should→loại 1, were→loại 2, had→loại 3, did→đảo ngữ phủ định.', $d);
        $this->matching($L, 'Match each sentence with its inversion type.', [['Should it rain, we stay.', 'conditional'], ['Never have I lied.', 'negative adverb'], ['Only then did I know.', '"only" phrase'], ['Had he come, we would win.', 'conditional']], 'Ba loại đảo ngữ: điều kiện, trạng từ phủ định, cụm "only".', $d);
        $this->sortQ($L, 'Drag each sentence into CONDITIONAL INVERSION or NEGATIVE-ADVERB INVERSION (set 2).', [['Were I rich, I would travel.', 'CONDITIONAL INVERSION'], ['Had he come, we would win.', 'CONDITIONAL INVERSION'], ['Never have I traveled.', 'NEGATIVE-ADVERB INVERSION'], ['Rarely does she go out.', 'NEGATIVE-ADVERB INVERSION']], 'Were/Had đầu câu = đảo điều kiện; never/rarely = đảo phủ định.', $d);
        $this->sortQ($L, 'Drag each sentence into CORRECT or INCORRECT inversion (set 2).', [['Should you need help, call me.', 'CORRECT'], ['Were I you, I would go.', 'CORRECT'], ['If should you need help, call me.', 'INCORRECT'], ['Had I wings, I will fly.', 'INCORRECT']], 'Bỏ "if" khi đảo; loại 3 dùng would.', $d);
        $this->sortQ($L, 'Drag each "if" out: which word replaces "if"?', [['If you should need', 'should'], ['If I were', 'were'], ['If he had gone', 'had'], ['If she comes', 'cannot invert']], 'should/were/had thay "if"; hiện tại đơn thường không đảo.', $d);
        $this->sortQ($L, 'Drag each sentence into WITH "IF" or WITHOUT "IF" (inversion) (set 2).', [['If he should come, tell me.', 'WITH "IF"'], ['If I were rich, I would help.', 'WITH "IF"'], ['Should he come, tell me.', 'WITHOUT "IF"'], ['Were I rich, I would help.', 'WITHOUT "IF"']], 'Đảo ngữ bỏ "if".', $d);
        $this->sortQ($L, 'Drag each sentence into TYPE 1, 2, 3 or NOT CONDITIONAL.', [['Should you see him, tell me.', 'TYPE 1'], ['Were I rich, I would travel.', 'TYPE 2'], ['Had he studied, he would pass.', 'TYPE 3'], ['Never have I lied.', 'NOT CONDITIONAL']], 'should→1, were→2, had→3.', $d);
        $this->fill($L, 'If she should call → ___ she call...', [[0, 'Should']], 'Bỏ if, đảo Should lên đầu.', $d);
        $this->fill($L, 'If they were here → ___ they here...', [[0, 'Were']], 'Bỏ if, đảo Were lên đầu.', $d);
        $this->fill($L, 'If we had known → ___ we known...', [[0, 'Had']], 'Bỏ if, đảo Had lên đầu.', $d);
        $this->fill($L, 'Only after the meeting ___ he speak. (did/does)', [[0, 'did']], 'Quá khứ → did.', $d);
        $this->fill($L, 'In inversion, "if" is ___ and the auxiliary moves forward. (dropped/kept)', [[0, 'dropped']], 'Đảo ngữ bỏ "if".', $d);
    }
}
