/**
 * php_launcher.c
 *
 * Script used to launch scripts on multiple processes
 *
 * argv[1]: script to run
 * argv[2]: number of processes to run
 * argv[3]: (optional) broker's IP to be set in broker.txt
 * argv[4]: (optional) broker's IP to be set in broker2.txt
 * 
 * Usage: ./php_launcher app_get_raw.php 8 108.80.210.36 108.80.210.37
 */

#include "utils.h"

#define RECEIVER_GET "receiver_get.php"
#define RECEIVER_MODIFY "receiver_modify.php"
#define RECEIVER_RENAME  "receiver_rename.php"
#define RECEIVER_SEARCH  "receiver_search.php"
#define BROKERTXT 	"broker.txt"
#define BROKER2TXT 	"broker2.txt"
#define MAX 		500

/**
 * Print out the program usage string
 */
#define USAGE(name) do {                                                                                                \
    fprintf(stderr,                                                                                                     \
        "\nUsage: %s SCRIPT.php N [IP] [IP2]\n"                               											\
        "\n"                                                                                                            \
        "Script used to launch a php script on multiple processes.\n"                       							\
        "\n"                                                                                                            \
        "SCRIPT.php                     Path to php script to run.\n"													\
        "\n"																											\
        "N                              Number of processes to run the script\n"										\
        "                               on. Max = 500 processes.\n"														\
        "\n"																											\
        "Option arguments:\n\n"                                                                                         \
        "[IP]                           IP address of a broker to connect to\n"											\
        "                               that will be written in 'broker.txt'.\n"										\
        "\n"																											\
        "[IP2]                          IP address of a second broker to\n"												\
        "                               connect to that will be written in\n"											\
        "                               'broker2.txt.\n"																\
        ,(name)                                                                                                         \
    );                                                                                                                  \
} while(0)

void setFile(const char *fileName, const char *msg)
{
	FILE *fd = Fopen(fileName, "w");

    fprintf(fd, msg, strlen(msg));

    fclose(fd);
}

void executeScripts(char *phpScript, int numProcesses)
{    
    pid_t child_pid;
    char *argv[] = {"/usr/bin/php", phpScript, 0};
    char *envp[] = {0};
    int i = 0;
    for (; i < numProcesses; i++)
    {
        child_pid = Fork();

        if (child_pid == 0)
        {
            // change stdout to log file
            char logFile[25];
            sprintf(logFile, "logs/%s%d.log", phpScript, i);

            int fd = Open(logFile, O_RDWR | O_CREAT, S_IRUSR | S_IWUSR);
            Dup2(fd, 1);   // make stdout go to file
            Close(fd);

            // execute script
            execve("/usr/bin/php", argv, envp);
            exit(0);
        }
        else
        {
            printf("Executing php script '%s' in process %d.\n", phpScript, i);
        }
    }
}

int main(int argc, char **argv)
{
	// if (argc < 3)
 //    {
	// 	USAGE(argv[0]);
 //        return EXIT_FAILURE;
 //    }

	// // check if we should set broker ip file(s)
	// if (argc > 3)
	// 	setFile(BROKERTXT, argv[3]);
	// if (argc > 4)
	// 	setFile(BROKER2TXT, argv[4]);

    // char *phpScript = argv[1];

    int numProcesses = atoi(argv[1]);
    if (numProcesses > MAX || numProcesses <= 0)
    {
        USAGE(argv[0]);
        return EXIT_FAILURE;
    }

    executeScripts(RECEIVER_GET, numProcesses);
//    executeScripts(RECEIVER_MODIFY, numProcesses);
//    executeScripts(RECEIVER_RENAME, numProcesses);
//    executeScripts(RECEIVER_SEARCH, numProcesses);

    return EXIT_SUCCESS;
}

