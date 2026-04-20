#include <stdio.h>
#include <string.h>
#include <stdbool.h>

#define MAX_STATES 10
#define MAX_SYMBOLS 10

int getIndex(char c, char symbols[], int numSymbols) {
    int i;
    for (i = 0; i < numSymbols; i++) {
        if (symbols[i] == c) {
            return i;
        }
    }
    return -1;
}

int main() {
    int STATES, SYMBOLS;
    int transition[MAX_STATES][MAX_SYMBOLS];
    char symbols[MAX_SYMBOLS];
    bool isFinal[MAX_STATES];
    int startState;
    char word[100];
    int i, j, numFinal, f;
    int currentState, len, col, nextState;
    char ch;

    // إدخال عدد الحالات
    printf("Enter number of states: ");
    scanf("%d", &STATES);

    // إدخال عدد الرموز
    printf("Enter number of symbols: ");
    scanf("%d", &SYMBOLS);

    // إدخال الرموز
    printf("Enter the symbols: ");
    for (i = 0; i < SYMBOLS; i++) {
        scanf(" %c", &symbols[i]);
    }

    // إدخال جدول الانتقالات
    printf("\n--- Enter Transition Table ---\n");
    for (i = 0; i < STATES; i++) {
        for (j = 0; j < SYMBOLS; j++) {
            printf("From state %d with symbol '%c' go to: ", i, symbols[j]);
            scanf("%d", &transition[i][j]);
        }
    }

    // إدخال الحالة البدائية
    printf("\nEnter start state: ");
    scanf("%d", &startState);

    // تهيئة الحالات النهائية
    for (i = 0; i < STATES; i++) {
        isFinal[i] = false;
    }

    // إدخال الحالات النهائية
    printf("Enter number of final states: ");
    scanf("%d", &numFinal);
    printf("Enter final states: ");
    for (i = 0; i < numFinal; i++) {
        scanf("%d", &f);
        isFinal[f] = true;
    }

    // إدخال الكلمة
    printf("\nEnter word to test: ");
    scanf("%s", word);

    // محاكاة الآلة
    currentState = startState;
    len = strlen(word);

    printf("\n--- Simulation ---\n");
    printf("Start: state %d\n", currentState);

    for (i = 0; i < len; i++) {
        ch = word[i];
        col = getIndex(ch, symbols, SYMBOLS);

        if (col == -1) {
            printf("Error: Character '%c' not in alphabet!\n", ch);
            printf("REJECTED\n");
            return 0;
        }

        nextState = transition[currentState][col];
        printf("Read '%c': %d -> %d\n", ch, currentState, nextState);
        currentState = nextState;
    }

    // النتيجة
    printf("\n--- Result ---\n");
    printf("End state: %d\n", currentState);

    if (isFinal[currentState] == true) {
        printf("ACCEPTED\n");
    } else {
        printf("REJECTED\n");
    }

    return 0;
}