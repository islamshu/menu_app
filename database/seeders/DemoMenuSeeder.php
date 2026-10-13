<?php

namespace Database\Seeders;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class DemoMenuSeeder extends Seeder
{
    /**
     * Slug of the confirmed demo restaurant this seeder targets.
     *
     * The seeder is intentionally scoped to this single restaurant so it can
     * never create or overwrite data on real tenants.
     */
    private const DEMO_SLUG = 'demo-restaurant';

    /**
     * Populate the demo restaurant with realistic, bilingual sample menu data.
     *
     * The seeder is idempotent: categories and items are matched by their
     * per-restaurant slug, so rerunning only updates existing records.
     */
    public function run(): void
    {
        $restaurant = Restaurant::where('slug', self::DEMO_SLUG)->first();

        if (! $restaurant) {
            $this->command?->warn(sprintf(
                'DemoMenuSeeder: no restaurant found with slug "%s"; skipping.',
                self::DEMO_SLUG,
            ));

            return;
        }

        $categories = $this->menu();
        $items = 0;

        foreach (array_values($categories) as $index => $category) {
            $model = MenuCategory::updateOrCreate(
                [
                    'restaurant_id' => $restaurant->id,
                    'slug' => $category['slug'],
                ],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'image' => null,
                ],
            );

            foreach (array_values($category['items']) as $position => $item) {
                MenuItem::updateOrCreate(
                    [
                        'restaurant_id' => $restaurant->id,
                        'slug' => $item['slug'],
                    ],
                    [
                        'menu_category_id' => $model->id,
                        'name' => $item['name'],
                        'description' => $item['description'],
                        'price' => $item['price'],
                        'compare_at_price' => $item['compare_at_price'] ?? null,
                        'image' => null,
                        'is_available' => true,
                        'is_featured' => $item['is_featured'] ?? false,
                        'is_popular' => $item['is_popular'] ?? false,
                        'is_new' => $item['is_new'] ?? false,
                        'sort_order' => $position + 1,
                    ],
                );

                $items++;
            }
        }

        $this->command?->info(sprintf(
            'DemoMenuSeeder: seeded %d categories and %d items for "%s" (images left as placeholders).',
            count($categories),
            $items,
            $restaurant->name,
        ));
    }

    /**
     * Bilingual (ar/en) demo menu definition with ILS prices.
     *
     * @return array<int, array<string, mixed>>
     */
    private function menu(): array
    {
        return [
            [
                'slug' => 'burgers',
                'name' => ['ar' => 'البرغر', 'en' => 'Burgers'],
                'description' => [
                    'ar' => 'قطع برغر طازجة تُشوى حسب الطلب مع الخبز والأطباق الجانبية.',
                    'en' => 'Fresh patties grilled to order, served with buns and sides.',
                ],
                'items' => [
                    [
                        'slug' => 'classic-cheeseburger',
                        'name' => ['ar' => 'تشيز برغر كلاسيك', 'en' => 'Classic Cheeseburger'],
                        'description' => [
                            'ar' => 'لحم بقري مشوي مع جبنة شيدر وصلصة خاصة وخبز بريوش.',
                            'en' => 'Grilled beef patty with cheddar, house sauce and a brioche bun.',
                        ],
                        'price' => 58,
                        'compare_at_price' => 68,
                        'is_featured' => true,
                        'is_popular' => true,
                    ],
                    [
                        'slug' => 'double-bacon-burger',
                        'name' => ['ar' => 'دبل بيكون برغر', 'en' => 'Double Bacon Burger'],
                        'description' => [
                            'ar' => 'قطعتا لحم مع لحم مقدد مدخن وجبنة شيدر.',
                            'en' => 'Double beef patty with smoked bacon and cheddar.',
                        ],
                        'price' => 74,
                        'is_popular' => true,
                    ],
                    [
                        'slug' => 'crispy-chicken-burger',
                        'name' => ['ar' => 'برغر دجاج مقرمش', 'en' => 'Crispy Chicken Burger'],
                        'description' => [
                            'ar' => 'دجاج مقرمش مع مخلل ومايونيز الثوم.',
                            'en' => 'Crispy chicken with pickles and garlic mayo.',
                        ],
                        'price' => 52,
                    ],
                    [
                        'slug' => 'mushroom-swiss-burger',
                        'name' => ['ar' => 'برغر المشروم والجبنة السويسرية', 'en' => 'Mushroom Swiss Burger'],
                        'description' => [
                            'ar' => 'لحم بقري مع مشروم مقلي وجبنة سويسرية.',
                            'en' => 'Beef patty with sauteed mushrooms and Swiss cheese.',
                        ],
                        'price' => 62,
                    ],
                    [
                        'slug' => 'veggie-burger',
                        'name' => ['ar' => 'برغر نباتي', 'en' => 'Veggie Burger'],
                        'description' => [
                            'ar' => 'قرص خضار مع أفوكادو وطماطم وخس.',
                            'en' => 'Veggie patty with avocado, tomato and lettuce.',
                        ],
                        'price' => 48,
                        'is_new' => true,
                    ],
                    [
                        'slug' => 'spicy-jalapeno-burger',
                        'name' => ['ar' => 'برغر هالبينو حار', 'en' => 'Spicy Jalapeno Burger'],
                        'description' => [
                            'ar' => 'لحم بقري مع هالبينو وجبنة بيبر جاك وصلصة حارة.',
                            'en' => 'Beef patty with jalapenos, pepper jack cheese and hot sauce.',
                        ],
                        'price' => 60,
                        'is_new' => true,
                    ],
                ],
            ],
            [
                'slug' => 'pizza',
                'name' => ['ar' => 'البيتزا', 'en' => 'Pizza'],
                'description' => [
                    'ar' => 'بيتزا إيطالية بعجينة رقيقة تُخبز في الفرن الحجري.',
                    'en' => 'Italian-style thin crust pizzas baked in a stone oven.',
                ],
                'items' => [
                    [
                        'slug' => 'margherita-pizza',
                        'name' => ['ar' => 'بيتزا مارغريتا', 'en' => 'Margherita Pizza'],
                        'description' => [
                            'ar' => 'صلصة طماطم وموزاريلا وريحان طازج.',
                            'en' => 'Tomato sauce, mozzarella and fresh basil.',
                        ],
                        'price' => 45,
                    ],
                    [
                        'slug' => 'pepperoni-pizza',
                        'name' => ['ar' => 'بيتزا ببروني', 'en' => 'Pepperoni Pizza'],
                        'description' => [
                            'ar' => 'ببروني حار مع موزاريلا وصلصة الطماطم.',
                            'en' => 'Spicy pepperoni with mozzarella and tomato sauce.',
                        ],
                        'price' => 55,
                        'is_featured' => true,
                        'is_popular' => true,
                    ],
                    [
                        'slug' => 'bbq-chicken-pizza',
                        'name' => ['ar' => 'بيتزا دجاج الباربكيو', 'en' => 'BBQ Chicken Pizza'],
                        'description' => [
                            'ar' => 'دجاج مشوي وصلصة باربكيو وبصل أحمر.',
                            'en' => 'Grilled chicken, BBQ sauce and red onion.',
                        ],
                        'price' => 58,
                    ],
                    [
                        'slug' => 'four-cheese-pizza',
                        'name' => ['ar' => 'بيتزا الأجبان الأربعة', 'en' => 'Four Cheese Pizza'],
                        'description' => [
                            'ar' => 'موزاريلا وشيدر وبارميزان وجبنة زرقاء.',
                            'en' => 'Mozzarella, cheddar, parmesan and blue cheese.',
                        ],
                        'price' => 62,
                    ],
                    [
                        'slug' => 'veggie-supreme-pizza',
                        'name' => ['ar' => 'بيتزا الخضار', 'en' => 'Veggie Supreme Pizza'],
                        'description' => [
                            'ar' => 'فلفل ومشروم وزيتون وطماطم كرزية.',
                            'en' => 'Peppers, mushrooms, olives and cherry tomato.',
                        ],
                        'price' => 50,
                        'is_new' => true,
                    ],
                    [
                        'slug' => 'tuna-pizza',
                        'name' => ['ar' => 'بيتزا التونة', 'en' => 'Tuna Pizza'],
                        'description' => [
                            'ar' => 'تونة وبصل وزيتون أسود مع الموزاريلا.',
                            'en' => 'Tuna, onion and black olives with mozzarella.',
                        ],
                        'price' => 56,
                    ],
                ],
            ],
            [
                'slug' => 'pasta',
                'name' => ['ar' => 'المكرونة', 'en' => 'Pasta'],
                'description' => [
                    'ar' => 'أطباق مكرونة تُحضّر بصلصات كريمية وطماطم تقليدية.',
                    'en' => 'Pasta dishes prepared with creamy and classic tomato sauces.',
                ],
                'items' => [
                    [
                        'slug' => 'fettuccine-alfredo',
                        'name' => ['ar' => 'فتوتشيني ألفريدو', 'en' => 'Fettuccine Alfredo'],
                        'description' => [
                            'ar' => 'فتوتشيني بصلصة الكريمة والبارميزان.',
                            'en' => 'Fettuccine in a creamy parmesan sauce.',
                        ],
                        'price' => 54,
                        'is_featured' => true,
                    ],
                    [
                        'slug' => 'spaghetti-bolognese',
                        'name' => ['ar' => 'سباغيتي بولونيز', 'en' => 'Spaghetti Bolognese'],
                        'description' => [
                            'ar' => 'سباغيتي مع صلصة اللحم الإيطالية.',
                            'en' => 'Spaghetti with Italian meat sauce.',
                        ],
                        'price' => 58,
                        'is_popular' => true,
                    ],
                    [
                        'slug' => 'pesto-penne',
                        'name' => ['ar' => 'بيني بالبيستو', 'en' => 'Pesto Penne'],
                        'description' => [
                            'ar' => 'بيني بصلصة البيستو والصنوبر.',
                            'en' => 'Penne with basil pesto and pine nuts.',
                        ],
                        'price' => 52,
                    ],
                    [
                        'slug' => 'beef-lasagna',
                        'name' => ['ar' => 'لازانيا باللحم', 'en' => 'Beef Lasagna'],
                        'description' => [
                            'ar' => 'طبقات اللازانيا مع اللحم والجبنة.',
                            'en' => 'Layered lasagna with beef and cheese.',
                        ],
                        'price' => 64,
                    ],
                    [
                        'slug' => 'penne-arrabbiata',
                        'name' => ['ar' => 'بيني أرابياتا', 'en' => 'Penne Arrabbiata'],
                        'description' => [
                            'ar' => 'بيني بصلصة الطماطم الحارة والثوم.',
                            'en' => 'Penne in a spicy tomato and garlic sauce.',
                        ],
                        'price' => 50,
                        'is_new' => true,
                    ],
                    [
                        'slug' => 'seafood-linguine',
                        'name' => ['ar' => 'لينغويني بالمأكولات البحرية', 'en' => 'Seafood Linguine'],
                        'description' => [
                            'ar' => 'لينغويني مع الروبيان والكاليماري في صلصة الطماطم.',
                            'en' => 'Linguine with shrimp and calamari in tomato sauce.',
                        ],
                        'price' => 72,
                        'is_new' => true,
                    ],
                ],
            ],
            [
                'slug' => 'appetizers',
                'name' => ['ar' => 'المقبلات', 'en' => 'Appetizers'],
                'description' => [
                    'ar' => 'مقبلات صغيرة للمشاركة قبل الطبق الرئيسي.',
                    'en' => 'Small sharing plates to start your meal.',
                ],
                'items' => [
                    [
                        'slug' => 'hummus',
                        'name' => ['ar' => 'حمص', 'en' => 'Hummus'],
                        'description' => [
                            'ar' => 'حمص بالطحينة مع زيت زيتون وخبز.',
                            'en' => 'Chickpea puree with tahini, olive oil and bread.',
                        ],
                        'price' => 24,
                    ],
                    [
                        'slug' => 'garlic-bread',
                        'name' => ['ar' => 'خبز بالثوم', 'en' => 'Garlic Bread'],
                        'description' => [
                            'ar' => 'خبز محمص بالثوم والزبدة والأعشاب.',
                            'en' => 'Toasted bread with garlic butter and herbs.',
                        ],
                        'price' => 22,
                    ],
                    [
                        'slug' => 'mozzarella-sticks',
                        'name' => ['ar' => 'أعواد الموزاريلا', 'en' => 'Mozzarella Sticks'],
                        'description' => [
                            'ar' => 'أعواد جبنة مقرمشة مع صلصة المارينارا.',
                            'en' => 'Crispy cheese sticks with marinara sauce.',
                        ],
                        'price' => 32,
                        'is_popular' => true,
                    ],
                    [
                        'slug' => 'chicken-wings',
                        'name' => ['ar' => 'أجنحة الدجاج', 'en' => 'Chicken Wings'],
                        'description' => [
                            'ar' => 'أجنحة دجاج مشوية بصلصة حارة.',
                            'en' => 'Grilled chicken wings in a hot sauce.',
                        ],
                        'price' => 38,
                        'is_featured' => true,
                    ],
                    [
                        'slug' => 'caprese-salad',
                        'name' => ['ar' => 'سلطة كابريزي', 'en' => 'Caprese Salad'],
                        'description' => [
                            'ar' => 'موزاريلا وطماطم وريحان مع زيت الزيتون.',
                            'en' => 'Mozzarella, tomato and basil with olive oil.',
                        ],
                        'price' => 30,
                    ],
                    [
                        'slug' => 'french-fries',
                        'name' => ['ar' => 'بطاطا مقلية', 'en' => 'French Fries'],
                        'description' => [
                            'ar' => 'بطاطا مقلية مقرمشة مع رشة ملح البحر.',
                            'en' => 'Crispy golden fries with sea salt.',
                        ],
                        'price' => 18,
                    ],
                ],
            ],
            [
                'slug' => 'drinks',
                'name' => ['ar' => 'المشروبات', 'en' => 'Drinks'],
                'description' => [
                    'ar' => 'مشروبات باردة وساخنة وعصائر طازجة.',
                    'en' => 'Cold drinks, hot drinks and fresh juices.',
                ],
                'items' => [
                    [
                        'slug' => 'fresh-orange-juice',
                        'name' => ['ar' => 'عصير برتقال طازج', 'en' => 'Fresh Orange Juice'],
                        'description' => [
                            'ar' => 'عصير برتقال طبيعي 100%.',
                            'en' => '100% natural orange juice.',
                        ],
                        'price' => 18,
                    ],
                    [
                        'slug' => 'lemon-mint',
                        'name' => ['ar' => 'ليمون بالنعنع', 'en' => 'Lemon and Mint'],
                        'description' => [
                            'ar' => 'عصير ليمون منعش مع النعنع الطازج.',
                            'en' => 'Refreshing lemonade with fresh mint.',
                        ],
                        'price' => 16,
                        'is_popular' => true,
                    ],
                    [
                        'slug' => 'soft-drink',
                        'name' => ['ar' => 'مشروب غازي', 'en' => 'Soft Drink'],
                        'description' => [
                            'ar' => 'كوكاكولا أو سفن أب حسب اختيارك.',
                            'en' => 'Coca-Cola or 7Up of your choice.',
                        ],
                        'price' => 12,
                    ],
                    [
                        'slug' => 'iced-latte',
                        'name' => ['ar' => 'آيس لاتيه', 'en' => 'Iced Latte'],
                        'description' => [
                            'ar' => 'قهوة مثلجة مع الحليب الطازج.',
                            'en' => 'Iced coffee with fresh milk.',
                        ],
                        'price' => 20,
                    ],
                    [
                        'slug' => 'mango-smoothie',
                        'name' => ['ar' => 'سموذي مانجو', 'en' => 'Mango Smoothie'],
                        'description' => [
                            'ar' => 'سموذي مانجو طبيعي مثلج.',
                            'en' => 'Frozen natural mango smoothie.',
                        ],
                        'price' => 22,
                        'is_new' => true,
                    ],
                    [
                        'slug' => 'still-water',
                        'name' => ['ar' => 'مياه معدنية', 'en' => 'Still Water'],
                        'description' => [
                            'ar' => 'زجاجة مياه معدنية 500 مل.',
                            'en' => 'Bottled mineral water, 500ml.',
                        ],
                        'price' => 8,
                    ],
                ],
            ],
            [
                'slug' => 'desserts',
                'name' => ['ar' => 'الحلويات', 'en' => 'Desserts'],
                'description' => [
                    'ar' => 'حلويات شرقية وغربية لإكمال وجبتك.',
                    'en' => 'Eastern and Western sweets to finish your meal.',
                ],
                'items' => [
                    [
                        'slug' => 'chocolate-cake',
                        'name' => ['ar' => 'كيك الشوكولاتة', 'en' => 'Chocolate Cake'],
                        'description' => [
                            'ar' => 'كيك شوكولاتة غني مع صوص الشوكولاتة.',
                            'en' => 'Rich chocolate cake with chocolate sauce.',
                        ],
                        'price' => 32,
                    ],
                    [
                        'slug' => 'new-york-cheesecake',
                        'name' => ['ar' => 'تشيز كيك', 'en' => 'New York Cheesecake'],
                        'description' => [
                            'ar' => 'تشيز كيك كريمي مع صوص التوت.',
                            'en' => 'Creamy cheesecake with berry sauce.',
                        ],
                        'price' => 30,
                        'is_featured' => true,
                    ],
                    [
                        'slug' => 'tiramisu',
                        'name' => ['ar' => 'تيراميسو', 'en' => 'Tiramisu'],
                        'description' => [
                            'ar' => 'حلوى إيطالية بنكهة القهوة والماسكربوني.',
                            'en' => 'Italian dessert with coffee and mascarpone.',
                        ],
                        'price' => 34,
                        'is_popular' => true,
                    ],
                    [
                        'slug' => 'apple-pie',
                        'name' => ['ar' => 'فطيرة التفاح', 'en' => 'Apple Pie'],
                        'description' => [
                            'ar' => 'فطيرة تفاح دافئة مع القرفة.',
                            'en' => 'Warm apple pie with cinnamon.',
                        ],
                        'price' => 28,
                    ],
                    [
                        'slug' => 'ice-cream',
                        'name' => ['ar' => 'آيس كريم', 'en' => 'Ice Cream'],
                        'description' => [
                            'ar' => 'ثلاث كرات آيس كريم حسب اختيارك.',
                            'en' => 'Three scoops of ice cream of your choice.',
                        ],
                        'price' => 24,
                    ],
                    [
                        'slug' => 'kunafa',
                        'name' => ['ar' => 'كنافة', 'en' => 'Kunafa'],
                        'description' => [
                            'ar' => 'كنافة بالجبنة مع القطر والفستق.',
                            'en' => 'Cheese kunafa with syrup and pistachio.',
                        ],
                        'price' => 30,
                        'is_new' => true,
                    ],
                ],
            ],
        ];
    }
}
