using System;
using System.Collections.Generic;
using System.Text.RegularExpressions;

namespace Sample.Utility
{
    public class UtilityHelper
    {
        /// <summary>
        ///     Finds the longest sequence of zeroes in a 
        ///     binary string derived from an integer
        /// </summary>
        /// <param name="n">Whole number</param>
        /// <returns>
        ///     Integer signifying the largest quantity of 
        ///     sequential zeroes.
        /// </returns>
        static int FindBinaryGap(int n)
        {
            string bin = Convert.ToString(n, 2);

            Regex regex = new Regex("[1(0+)1]+");

            Match match = regex.Match(bin);

            string[] groups = match.Value.Split('1');

            int highest = 0;

            for (int i = 0; i < groups.Length; i++)
            {
                if (groups[i].Length > highest)
                    highest = groups[i].Length;
            }

            return highest;
        }

        /// <summary>
        ///     Shifts elements of an integer array k places to the right
        /// </summary>
        /// <param name="A">The array</param>
        /// <param name="k">Shift delta</param>
        /// <returns>
        ///     Shifted integer array
        /// </returns>
        static int[] ShiftArray(int[] A, int k)
        {
            int length = A.Length;
            int[] newArray = new int[length];
            for (int i = 0; i < length; i++)
            {
                int shift = i + k;

                if (shift >= length)
                {
                    int index = (0 + (shift - length));
                    newArray[index] = A[i];
                }
                else
                    newArray[i + k] = A[i];
            }

            return newArray;
        }

        /// <summary>
        ///     Determines the value of an array that 
        ///     doesn't have a matching pair
        /// </summary>
        /// <param name="A">Integer array</param>
        /// <returns>
        ///     The value of the unpaired element
        /// </returns>
        static int FindUnpairedIndex(int[] A)
        {
            int unpaired = 0;
            foreach (int i in A)
            {
                unpaired ^= i;
            }

            return unpaired;
        }

        /// <summary>
        ///     Verifies that the provided side lengths 
        ///     can create a valid triangle.
        /// </summary>
        /// <param name="a">Side A</param>
        /// <param name="b">Side B</param>
        /// <param name="c">Side C</param>
        /// <returns>
        ///     True if valid, false if invalid
        /// </returns>
        static bool isTriangle(int a, int b, int c)
        {
            if (a > 0 && b > 0 && c > 0)
                return (((a + b) > c) && ((b + c) > a) && ((a + c) > b)) ? true : false;
            else
                return false;
        }

        /// <summary>
        ///     Takes a string and converts it to a paired
        ///     string array
        /// </summary>
        /// <param name="str">Input String</param>
        /// <returns>A string array of paired characters.</returns>
        static string[] PairString(string str)
        {
            if (str != string.Empty)
            {
                char[] chars = str.ToCharArray();
                List<string> pairList = new List<string>();

                for (int i = 0; i < chars.Length; i++)
                {
                    if (i % 2 != 0 && i != 0)
                        pairList.Add(chars[(i - 1)].ToString() + chars[i].ToString());
                }

                if (chars.Length % 2 != 0)
                    pairList.Add(chars[chars.Length - 1].ToString() + "_");

                return pairList.ToArray();

            }
            else
                return null;
        }
    }
}
