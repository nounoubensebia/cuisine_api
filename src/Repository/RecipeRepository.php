<?php

namespace App\Repository;

use App\Entity\Ingredient;
use App\Entity\Recipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Recipe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recipe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recipe[]    findAll()
 * @method Recipe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeRepository extends ServiceEntityRepository
{
    private $manager;

    private $ingredientRepository;

    public function __construct
    (ManagerRegistry $registry,EntityManagerInterface $manager,IngredientRepository $ingredientRepository)
    {
        parent::__construct($registry, Recipe::class);
        $this->manager = $manager;
        $this->ingredientRepository = $ingredientRepository;
    }


    public function findById ($id)
    {
        $recipe = $this->manager->find(Recipe::class,$id);
        return $recipe;
    }

    public function saveRecipe($title,$sub_title,$ingredients)
    {
        $recipe = new Recipe();
        $recipe->setTitle($title);
        $recipe->setSubTitle($sub_title);

        foreach ($ingredients as $ingredient)
        {
            $ingredientObj = $this->ingredientRepository->findByName($ingredient['name']);
            if ($ingredientObj == null)
            {
                $ingredientObj = new Ingredient();
                $ingredientObj->setName($ingredient['name']);
            }
            //$ingredientObj->addRecipe($recipe);
            $recipe->addIngredient($ingredientObj);
        }

        $this->manager->persist($recipe);
        $this->manager->flush();

        return $recipe;

    }

    public function updateRecipe($id,$title,$sub_title,$ingredients)
    {
        $recipe = $this->manager->find(Recipe::class,$id);

        /**
         * @var $recipe Recipe
         */

        if($title!=null)
        {
            $recipe->setTitle($title);
        }
        if ($sub_title!=null)
        {
            $recipe->setSubTitle($sub_title);
        }
        if ($ingredients!=null)
        {
            $ingredientsObjects = $recipe->getIngredients();

            //removing missing ingredients

            foreach ($ingredientsObjects as $ingredientObj)
            {
                if (!array_search($ingredientObj->getName(),array_column($ingredients,"name")))
                {
                    $recipe->removeIngredient($ingredientObj);
                }
            }

            //adding new ingredients

            foreach ($ingredients as $ingredient)
            {
                $ingredientObj = $this->ingredientRepository->findByName($ingredient['name']);

                if ($ingredientObj==null)
                {
                    $ingredientObj = new Ingredient();
                    $ingredientObj->setName($ingredient['name']);
                    $recipe->addIngredient($ingredientObj);
                }

                else
                {
                    if (!$ingredientsObjects->contains($ingredientObj))
                    {
                        $recipe->addIngredient($ingredientObj);
                    }
                }

            }
        }
        $this->manager->persist($recipe);
        $this->manager->flush();
    }

    public function deleteRecipe(Recipe $recipe)
    {
        $this->manager->remove($recipe);
        $this->manager->flush();
    }
}
